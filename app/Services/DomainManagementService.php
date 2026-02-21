<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Tenant;
use App\Repositories\Contracts\DomainRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DomainManagementService
{
    /** Subdomains that may never be registered by tenants. */
    private const RESERVED_SUBDOMAINS = [
        'www', 'mail', 'admin', 'api', 'app', 'blog', 'shop',
        'store', 'support', 'help', 'docs', 'dev', 'staging',
        'prod', 'test', 'demo', 'm', 'mobile', 'static', 'cdn',
        'assets', 'dashboard', 'portal', 'login', 'register',
    ];

    public function __construct(
        private readonly DomainRepositoryInterface $domainRepository,
        private readonly DomainVerificationService $verificationService,
    ) {}

    /**
     * Return all domains for the given tenant, formatted for the API.
     */
    public function listForTenant(Tenant $tenant): Collection
    {
        return $this->domainRepository
            ->getForTenant($tenant)
            ->map(fn (Domain $domain) => [
                'id'             => $domain->id,
                'name'           => $domain->domain,
                'type'           => $domain->isSubdomain() ? 'subdomain' : 'custom',
                'is_primary'     => $domain->is_primary,
                'verified'       => $domain->verified,
                'verified_at'    => $domain->verified_at,
                'ssl_status'     => $domain->ssl_status,
                'ssl_expires_at' => $domain->ssl_expires_at,
                'created_at'     => $domain->created_at,
            ]);
    }

    /**
     * Add a new domain to a tenant.
     *
     * @return array{domain: Domain, verification_instructions?: array}
     * @throws \RuntimeException when the domain is already taken
     */
    public function addDomain(Tenant $tenant, string $domainName, bool $isPrimary = false): array
    {
        if ($this->domainRepository->existsByName($domainName)) {
            throw new \RuntimeException('This domain is already registered to another tenant.', 409);
        }

        $isSubdomain = str_ends_with($domainName, '.sater.com');
        $domainType  = $isSubdomain ? 'subdomain' : 'custom';

        return DB::transaction(function () use ($tenant, $domainName, $isPrimary, $isSubdomain, $domainType) {
            $domain = $this->domainRepository->create([
                'domain'      => $domainName,
                'tenant_id'   => $tenant->id,
                'verified'    => $isSubdomain,
                'verified_at' => $isSubdomain ? now() : null,
                'type'        => $domainType,
                'ssl_status'  => $isSubdomain ? 'active' : 'pending',
                'is_primary'  => $isPrimary,
            ]);

            if (! $isSubdomain) {
                $domain->generateVerificationToken();
            }

            if ($domain->is_primary) {
                $this->domainRepository->unsetOtherPrimaries($tenant, $domain);
            }

            $result = ['domain' => $domain];

            if (! $isSubdomain) {
                $result['verification_instructions'] = $domain->getVerificationInstructions();
            }

            return $result;
        });
    }

    /**
     * Verify domain ownership via DNS and provision SSL on success.
     *
     * @return Domain The updated domain model
     * @throws \RuntimeException when already verified or verification fails
     */
    public function verifyDomain(Domain $domain): Domain
    {
        if ($domain->verified) {
            throw new \RuntimeException('Domain is already verified.', 0);
        }

        $verified = $domain->verify();

        if (! $verified) {
            throw new \RuntimeException(
                "Domain verification failed. DNS record not found. Ensure you've added the TXT record and wait up to 5 minutes for DNS propagation.",
                400
            );
        }

        $this->verificationService->provisionSSL($domain);

        return $domain->refresh();
    }

    /**
     * Set a verified domain as the tenant's primary domain.
     *
     * @throws \RuntimeException when domain is not verified
     */
    public function setPrimary(Domain $domain): Domain
    {
        if (! $domain->verified) {
            throw new \RuntimeException('Cannot set unverified domain as primary.', 400);
        }

        DB::transaction(function () use ($domain) {
            $this->domainRepository->unsetOtherPrimaries($domain->tenant, $domain);
            $this->domainRepository->update($domain, ['is_primary' => true]);
        });

        return $domain->refresh();
    }

    /**
     * Delete a non-primary, non-subdomain domain.
     *
     * @throws \RuntimeException when deletion is not allowed
     */
    public function deleteDomain(Domain $domain): void
    {
        if ($domain->is_primary) {
            throw new \RuntimeException(
                'Cannot delete primary domain. Set another domain as primary first.',
                400
            );
        }

        if ($domain->isSubdomain()) {
            throw new \RuntimeException(
                'Cannot delete subdomain. Use the subdomain change endpoint instead.',
                400
            );
        }

        $this->domainRepository->delete($domain);
    }

    /**
     * Change a tenant's subdomain (one free change per tenant).
     *
     * @return array{old_url: string, new_url: string, subdomain: string, subdomain_changed_at: \Carbon\Carbon}
     * @throws \RuntimeException on any business-rule violation
     */
    public function changeSubdomain(Tenant $tenant, string $subdomain): array
    {
        if ($tenant->subdomain_changed_at) {
            throw new \RuntimeException('You have already used your free subdomain change.', 400);
        }

        $newSubdomain = "{$subdomain}.sater.com";

        if ($this->domainRepository->existsByName($newSubdomain)) {
            throw new \RuntimeException('This subdomain is already taken.', 409);
        }

        if ($this->isReserved($subdomain)) {
            throw new \RuntimeException("The subdomain '{$subdomain}' is reserved.", 422);
        }

        return DB::transaction(function () use ($tenant, $subdomain, $newSubdomain) {
            $oldDomain    = $this->domainRepository->getSubdomainForTenant($tenant);
            $oldSubdomain = $oldDomain?->domain;

            if ($oldDomain) {
                $this->domainRepository->update($oldDomain, ['domain' => $newSubdomain]);
            } else {
                $this->domainRepository->create([
                    'domain'     => $newSubdomain,
                    'tenant_id'  => $tenant->id,
                    'verified'   => true,
                    'type'       => 'subdomain',
                    'is_primary' => true,
                ]);
            }

            $tenant->update(['subdomain_changed_at' => now()]);

            Log::info('Tenant subdomain changed', [
                'tenant_id'     => $tenant->id,
                'old_subdomain' => $oldSubdomain,
                'new_subdomain' => $newSubdomain,
            ]);

            return [
                'subdomain'            => $newSubdomain,
                'subdomain_changed_at' => now(),
                'old_url'              => "https://{$oldSubdomain}",
                'new_url'              => "https://{$newSubdomain}",
            ];
        });
    }

    private function isReserved(string $subdomain): bool
    {
        return in_array(strtolower($subdomain), self::RESERVED_SUBDOMAINS, strict: true);
    }
}
