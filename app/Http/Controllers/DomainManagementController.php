<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDomainRequest;
use App\Models\Domain;
use App\Models\Tenant;
use App\Services\DomainVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Domain Management Controller
 * 
 * Handles tenant domain management including subdomain changes,
 * custom domain addition, DNS verification, and SSL provisioning.
 */
class DomainManagementController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected DomainVerificationService $verificationService,
    ) {
    }

    /**
     * List all domains for a tenant.
     * 
     * @param Request $request
     * @param string $tenantId
     * @return JsonResponse
     */
    public function index(Request $request, string $tenantId): JsonResponse
    {
        $tenant = Tenant::findOrFail($tenantId);
        
        $domains = $tenant->domains()
            ->orderBy('is_primary', 'desc')
            ->orderBy('domain')
            ->get()
            ->map(function ($domain) {
                return [
                    'id' => $domain->id,
                    'name' => $domain->domain,
                    'type' => $domain->isSubdomain() ? 'subdomain' : 'custom',
                    'is_primary' => $domain->is_primary,
                    'verified' => $domain->verified,
                    'verified_at' => $domain->verified_at,
                    'ssl_status' => $domain->ssl_status,
                    'ssl_expires_at' => $domain->ssl_expires_at,
                    'created_at' => $domain->created_at,
                ];
            });
        
        return response()->json([
            'data' => $domains,
        ]);
    }

    /**
     * Add a new domain to tenant.
     * 
     * @param UpdateDomainRequest $request
     * @param string $tenantId
     * @return JsonResponse
     */
    public function store(UpdateDomainRequest $request, string $tenantId): JsonResponse
    {
        $validated = $request->validated();
        
        $tenant = Tenant::findOrFail($tenantId);
        
        DB::beginTransaction();
        
        try {
            // Check if domain already exists
            $existingDomain = Domain::where('domain', $validated['domain'])->first();
            
            if ($existingDomain) {
                return response()->json([
                    'message' => 'This domain is already registered to another tenant.',
                    'code' => 'DOMAIN_EXISTS',
                ], 409);
            }
            
            // Determine domain type
            $isSubdomain = str_ends_with($validated['domain'], '.sater.com');
            $domainType = $isSubdomain ? 'subdomain' : 'custom';
            
            // Create domain
            $domain = Domain::create([
                'domain' => $validated['domain'],
                'tenant_id' => $tenant->id,
                'verified' => $isSubdomain, // Auto-verify subdomains
                'verified_at' => $isSubdomain ? now() : null,
                'type' => $domainType,
                'ssl_status' => $isSubdomain ? 'active' : 'pending',
                'is_primary' => $validated['is_primary'] ?? false,
            ]);
            
            // If custom domain, generate verification token
            if (!$isSubdomain) {
                $domain->generateVerificationToken();
            }
            
            // If this is set as primary, unset other primaries
            if ($domain->is_primary) {
                $tenant->domains()
                    ->where('id', '!=', $domain->id)
                    ->update(['is_primary' => false]);
            }
            
            DB::commit();
            
            $response = [
                'message' => 'Domain added successfully.',
                'domain' => [
                    'id' => $domain->id,
                    'name' => $domain->domain,
                    'type' => $domainType,
                    'verified' => $domain->verified,
                    'is_primary' => $domain->is_primary,
                ],
            ];
            
            // Add verification instructions for custom domains
            if (!$isSubdomain) {
                $response['verification_instructions'] = $domain->getVerificationInstructions();
            }
            
            return response()->json($response, 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Failed to add domain', [
                'tenant_id' => $tenantId,
                'domain' => $validated['domain'],
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Failed to add domain. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Verify domain ownership via DNS.
     * 
     * @param string $domainId
     * @return JsonResponse
     */
    public function verify(string $domainId): JsonResponse
    {
        $domain = Domain::findOrFail($domainId);
        
        if ($domain->verified) {
            return response()->json([
                'message' => 'Domain is already verified.',
                'domain' => [
                    'id' => $domain->id,
                    'verified' => true,
                    'verified_at' => $domain->verified_at,
                ],
            ]);
        }
        
        try {
            $verified = $domain->verify();
            
            if ($verified) {
                // Trigger SSL provisioning (would integrate with Let's Encrypt or similar)
                // For now, just update status
                $domain->update([
                    'ssl_status' => 'active',
                    'ssl_expires_at' => now()->addMonths(3),
                ]);
                
                return response()->json([
                    'message' => 'Domain verified successfully. SSL certificate is being provisioned.',
                    'domain' => [
                        'id' => $domain->id,
                        'verified' => true,
                        'verified_at' => $domain->verified_at,
                        'ssl_status' => 'active',
                    ],
                ]);
            }
            
            return response()->json([
                'message' => 'Domain verification failed. DNS record not found.',
                'code' => 'VERIFICATION_FAILED',
                'hint' => 'Ensure you\'ve added the TXT record and wait up to 5 minutes for DNS propagation.',
            ], 400);
            
        } catch (\Exception $e) {
            \Log::error('Domain verification failed', [
                'domain_id' => $domainId,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Domain verification failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Set domain as primary.
     * 
     * @param string $domainId
     * @return JsonResponse
     */
    public function setPrimary(string $domainId): JsonResponse
    {
        $domain = Domain::findOrFail($domainId);
        
        if (!$domain->verified) {
            return response()->json([
                'message' => 'Cannot set unverified domain as primary.',
                'code' => 'DOMAIN_NOT_VERIFIED',
            ], 400);
        }
        
        DB::transaction(function () use ($domain) {
            // Unset all other primaries for this tenant
            $domain->tenant->domains()
                ->where('id', '!=', $domain->id)
                ->update(['is_primary' => false]);
            
            // Set this as primary
            $domain->update(['is_primary' => true]);
        });
        
        return response()->json([
            'message' => 'Primary domain updated successfully.',
            'domain' => [
                'id' => $domain->id,
                'name' => $domain->domain,
                'is_primary' => true,
            ],
        ]);
    }

    /**
     * Remove a domain.
     * 
     * @param string $domainId
     * @return JsonResponse
     */
    public function destroy(string $domainId): JsonResponse
    {
        $domain = Domain::findOrFail($domainId);
        
        // Cannot delete primary domain
        if ($domain->is_primary) {
            return response()->json([
                'message' => 'Cannot delete primary domain. Set another domain as primary first.',
                'code' => 'CANNOT_DELETE_PRIMARY',
            ], 400);
        }
        
        // Cannot delete subdomain (must use subdomain change endpoint)
        if ($domain->isSubdomain()) {
            return response()->json([
                'message' => 'Cannot delete subdomain. Use the subdomain change endpoint instead.',
                'code' => 'CANNOT_DELETE_SUBDOMAIN',
            ], 400);
        }
        
        $domain->delete();
        
        return response()->json([
            'message' => 'Domain removed successfully.',
        ]);
    }

    /**
     * Change tenant subdomain (once free).
     * 
     * @param Request $request
     * @param string $tenantId
     * @return JsonResponse
     */
    public function changeSubdomain(Request $request, string $tenantId): JsonResponse
    {
        $validated = $request->validate([
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9]+([\-][a-z0-9]+)*$/',
            ],
        ]);
        
        $tenant = Tenant::findOrFail($tenantId);
        
        // Check if already used free change
        if ($tenant->subdomain_changed_at) {
            return response()->json([
                'message' => 'You have already used your free subdomain change.',
                'code' => 'SUBDOMAIN_CHANGE_LIMIT_EXCEEDED',
                'changed_at' => $tenant->subdomain_changed_at,
            ], 400);
        }
        
        $newSubdomain = $validated['subdomain'] . '.sater.com';
        
        DB::beginTransaction();
        
        try {
            // Check if subdomain is available
            $existingDomain = Domain::where('domain', $newSubdomain)->first();
            
            if ($existingDomain) {
                return response()->json([
                    'message' => 'This subdomain is already taken.',
                    'code' => 'SUBDOMAIN_EXISTS',
                ], 409);
            }
            
            // Check reserved subdomains
            if ($this->isReservedSubdomain($validated['subdomain'])) {
                return response()->json([
                    'message' => "The subdomain '{$validated['subdomain']}' is reserved.",
                    'code' => 'RESERVED_SUBDOMAIN',
                ], 422);
            }
            
            // Get old subdomain
            $oldDomain = $tenant->domains()->where('type', 'subdomain')->first();
            $oldSubdomain = $oldDomain?->domain;
            
            // Update or create new domain
            if ($oldDomain) {
                $oldDomain->update(['domain' => $newSubdomain]);
            } else {
                Domain::create([
                    'domain' => $newSubdomain,
                    'tenant_id' => $tenant->id,
                    'verified' => true,
                    'type' => 'subdomain',
                    'is_primary' => true,
                ]);
            }
            
            // Mark that subdomain has been changed
            $tenant->update([
                'subdomain_changed_at' => now(),
            ]);
            
            DB::commit();
            
            \Log::info('Tenant subdomain changed', [
                'tenant_id' => $tenant->id,
                'old_subdomain' => $oldSubdomain,
                'new_subdomain' => $newSubdomain,
            ]);
            
            return response()->json([
                'message' => 'Subdomain changed successfully. Your store is now accessible at ' . $newSubdomain,
                'tenant' => [
                    'subdomain' => $newSubdomain,
                    'subdomain_changed_at' => now(),
                ],
                'old_url' => "https://{$oldSubdomain}",
                'new_url' => "https://{$newSubdomain}",
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Subdomain change failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Failed to change subdomain. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Check if subdomain is reserved.
     */
    protected function isReservedSubdomain(string $subdomain): bool
    {
        $reserved = [
            'www', 'mail', 'admin', 'api', 'app', 'blog', 'shop', 
            'store', 'support', 'help', 'docs', 'dev', 'staging', 
            'prod', 'test', 'demo', 'm', 'mobile', 'static', 'cdn', 
            'assets', 'dashboard', 'portal', 'login', 'register',
        ];
        
        return in_array(strtolower($subdomain), $reserved);
    }
}
