<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterTenantRequest;
use App\Models\Domain;
use App\Models\EmailVerification;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Events\TenantCreated;

/**
 * Tenant Registration Controller
 * 
 * Handles tenant registration, email verification, and subscription selection.
 */
class TenantRegistrationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected EmailVerificationService $verificationService,
    ) {
    }

    /**
     * Register a new tenant.
     * 
     * @param RegisterTenantRequest $request
     * @return JsonResponse
     */
    public function register(RegisterTenantRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        DB::beginTransaction();
        
        try {
            // Create tenant
            $tenant = Tenant::create([
                'store_name' => $validated['store_name'],
                'email' => $validated['email'],
                'password_hash' => $validated['password'],
                'language' => $validated['language'],
                'status' => 'pending_email_verification',
            ]);
            
            // Create domain (subdomain)
            $domain = Domain::create([
                'domain' => $request->getFullSubdomain(),
                'tenant_id' => $tenant->id,
                'verified' => false, // Will be verified after email verification
                'type' => 'subdomain',
                'is_primary' => true,
            ]);
            
            // Create email verification and send email
            $verification = $this->verificationService->createVerificationAndSend($tenant);
            
            DB::commit();
            
            // Log the registration event (optional, for analytics)
            \Log::info('New tenant registered', [
                'tenant_id' => $tenant->id,
                'store_name' => $tenant->store_name,
                'email' => $tenant->email,
                'subdomain' => $domain->domain,
            ]);
            
            return response()->json([
                'message' => 'Registration successful. Please check your email to verify your account.',
                'tenant' => [
                    'id' => $tenant->id,
                    'store_name' => $tenant->store_name,
                    'subdomain' => $domain->domain,
                    'email' => $tenant->email,
                    'language' => $tenant->language,
                    'status' => $tenant->status,
                ],
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up tenant if created
            if (isset($tenant)) {
                $tenant->delete(); // This will trigger database deletion event
            }
            
            \Log::error('Tenant registration failed', [
                'error' => $e->getMessage(),
                'email' => $validated['email'] ?? null,
            ]);
            
            return response()->json([
                'message' => 'Registration failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Verify tenant email address.
     * 
     * @param string $token
     * @return JsonResponse
     */
    public function verify(string $token): JsonResponse
    {
        $tenant = $this->verificationService->verifyToken($token);
        
        if (!$tenant) {
            return response()->json([
                'message' => 'Invalid or expired verification token.',
                'code' => 'INVALID_TOKEN',
            ], 404);
        }
        
        // Get the primary domain
        $domain = $tenant->domains()->first();
        
        return response()->json([
            'message' => 'Email verified successfully. Please select a subscription plan.',
            'tenant' => [
                'id' => $tenant->id,
                'store_name' => $tenant->store_name,
                'subdomain' => $domain?->domain,
                'status' => $tenant->status,
            ],
            'redirect_url' => "https://{$domain?->domain}/onboarding/plan-selection",
        ]);
    }

    /**
     * Resend verification email.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:tenants,email',
        ]);
        
        $tenant = Tenant::where('email', $validated['email'])->first();
        
        if ($tenant->status === 'active') {
            return response()->json([
                'message' => 'Email is already verified.',
            ], 400);
        }
        
        $this->verificationService->resendVerification($tenant);
        
        return response()->json([
            'message' => 'Verification email sent successfully.',
        ]);
    }

    /**
     * List available subscription plans.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function listPlans(Request $request): JsonResponse
    {
        $plans = SubscriptionPlan::active()
            ->ordered()
            ->get()
            ->map(function ($plan) use ($request) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'description' => $plan->description,
                    'price' => [
                        'monthly' => $plan->price_monthly,
                        'yearly' => $plan->price_yearly,
                        'currency' => 'SAR',
                    ],
                    'features' => $plan->features,
                    'trial_days' => $plan->trial_days,
                    'is_active' => $plan->is_active,
                ];
            });
        
        return response()->json([
            'data' => $plans,
        ]);
    }

    /**
     * Subscribe tenant to a plan.
     * 
     * @param Request $request
     * @param string $tenantId
     * @return JsonResponse
     */
    public function subscribe(Request $request, string $tenantId): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|uuid|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);
        
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found.',
                'code' => 'TENANT_NOT_FOUND',
            ], 404);
        }
        
        $plan = SubscriptionPlan::find($validated['plan_id']);
        
        DB::beginTransaction();
        
        try {
            // Create subscription
            $subscription = $tenant->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'active',
                'billing_cycle' => $validated['billing_cycle'],
                'starts_at' => now(),
                'ends_at' => null,
                'trial_ends_at' => $plan->trial_days > 0 
                    ? now()->addDays($plan->trial_days) 
                    : null,
                'currency' => 'SAR',
                'amount' => $validated['billing_cycle'] === 'yearly' 
                    ? $plan->price_yearly 
                    : $plan->price_monthly,
            ]);
            
            // Update tenant's current plan
            $tenant->update([
                'current_plan_id' => $plan->id,
            ]);
            
            DB::commit();
            
            // Get the primary domain
            $domain = $tenant->domains()->first();
            
            \Log::info('Tenant subscribed to plan', [
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
            ]);
            
            return response()->json([
                'message' => 'Subscription activated successfully.',
                'subscription' => [
                    'id' => $subscription->id,
                    'tenant_id' => $tenant->id,
                    'plan' => [
                        'id' => $plan->id,
                        'name' => $plan->name,
                    ],
                    'status' => $subscription->status,
                    'billing_cycle' => $subscription->billing_cycle,
                    'amount' => $subscription->amount,
                    'currency' => $subscription->currency,
                    'starts_at' => $subscription->starts_at,
                    'trial_ends_at' => $subscription->trial_ends_at,
                ],
                'redirect_url' => "https://{$domain?->domain}/dashboard",
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Subscription failed', [
                'tenant_id' => $tenantId,
                'plan_id' => $validated['plan_id'],
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Subscription failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
