<?php

namespace App\Services;

use App\Models\Domain;
use Illuminate\Support\Facades\Http;

/**
 * Domain Verification Service
 * 
 * Handles DNS verification and SSL provisioning for custom domains.
 */
class DomainVerificationService
{
    /**
     * Verify domain ownership via DNS TXT record.
     * 
     * @param Domain $domain
     * @return bool True if verification succeeded
     */
    public function verifyDomain(Domain $domain): bool
    {
        return $domain->verify();
    }

    /**
     * Provision SSL certificate for a verified domain.
     * 
     * This would integrate with Let's Encrypt, Laravel Ploi, or Forge.
     * For now, this is a placeholder for the integration.
     * 
     * @param Domain $domain
     * @return bool True if provisioning succeeded
     */
    public function provisionSSL(Domain $domain): bool
    {
        if (!$domain->verified) {
            throw new \Exception('Cannot provision SSL for unverified domain');
        }

        // Integration with Laravel Ploi (example)
        // $this->provisionSSLPloi($domain);

        // Integration with Let's Encrypt via acme-php
        // $this->provisionSSLLetsEncrypt($domain);

        // For now, mark as active (in production, this would be async)
        $domain->update([
            'ssl_status' => 'active',
            'ssl_expires_at' => now()->addMonths(3),
        ]);

        \Log::info('SSL certificate provisioned', [
            'domain_id' => $domain->id,
            'domain' => $domain->domain,
            'expires_at' => $domain->ssl_expires_at,
        ]);

        return true;
    }

    /**
     * Renew SSL certificate for a domain.
     * 
     * @param Domain $domain
     * @return bool True if renewal succeeded
     */
    public function renewSSL(Domain $domain): bool
    {
        if (!$domain->verified) {
            return false;
        }

        // Check if SSL is expiring soon (within 30 days)
        if ($domain->ssl_expires_at && $domain->ssl_expires_at->diffInDays(now()) > 30) {
            return true; // Not yet time to renew
        }

        return $this->provisionSSL($domain);
    }

    /**
     * Check DNS propagation for a domain.
     * 
     * @param Domain $domain
     * @return bool True if DNS is properly configured
     */
    public function checkDNSPropagation(Domain $domain): bool
    {
        // Check for TXT record
        $txtRecords = @dns_get_record($domain->domain, DNS_TXT);
        
        if ($txtRecords === false) {
            return false;
        }

        $expectedValue = "sater-verify={$domain->verification_token}";
        
        foreach ($txtRecords as $record) {
            if (isset($record['txt']) && strpos($record['txt'], 'sater-verify=') === 0) {
                if ($record['txt'] === $expectedValue) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get DNS configuration instructions for a domain.
     * 
     * @param Domain $domain
     * @return array<string, string> DNS record details
     */
    public function getDNSInstructions(Domain $domain): array
    {
        if (!$domain->verification_token) {
            $domain->generateVerificationToken();
        }

        return [
            'txt_record' => [
                'type' => 'TXT',
                'name' => "_sater-verification.{$domain->domain}",
                'value' => "sater-verify={$domain->verification_token}",
                'ttl' => 3600,
            ],
            'cname_record' => [
                'type' => 'CNAME',
                'name' => "www.{$domain->domain}",
                'value' => "{$domain->domain}",
                'ttl' => 3600,
            ],
            'a_record' => [
                'type' => 'A',
                'name' => "@",
                'value' => $this->getPlatformIPAddress(),
                'ttl' => 3600,
            ],
        ];
    }

    /**
     * Get platform IP address for A record.
     * 
     * @return string Platform IP address
     */
    protected function getPlatformIPAddress(): string
    {
        return config('app.platform_ip', '127.0.0.1');
    }

    /**
     * Schedule SSL renewal check.
     * 
     * This would be called by a scheduled task to check
     * all domains for upcoming SSL expirations.
     * 
     * @return void
     */
    public function scheduleRenewalChecks(): void
    {
        $domains = Domain::where('ssl_status', 'active')
            ->where(function ($query) {
                $query->whereNull('ssl_expires_at')
                    ->orWhere('ssl_expires_at', '<=', now()->addDays(30));
            })
            ->get();

        foreach ($domains as $domain) {
            try {
                $this->renewSSL($domain);
            } catch (\Exception $e) {
                \Log::error('SSL renewal failed', [
                    'domain_id' => $domain->id,
                    'domain' => $domain->domain,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Integration with Laravel Ploi for SSL provisioning.
     * 
     * @param Domain $domain
     * @return void
     */
    protected function provisionSSLPloi(Domain $domain): void
    {
        $apiKey = config('services.ploi.api_key');
        $siteId = config('services.ploi.site_id');

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Accept' => 'application/json',
        ])->post("https://ploi.io/api/sites/{$siteId}/certificates", [
            'domain' => $domain->domain,
            'source' => 'letsencrypt',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $domain->update([
                'ssl_status' => 'active',
                'ssl_expires_at' => now()->addMonths(3),
            ]);
        } else {
            throw new \Exception('Ploi SSL provisioning failed: ' . $response->body());
        }
    }

    /**
     * Integration with Let's Encrypt via acme-php.
     * 
     * @param Domain $domain
     * @return void
     */
    protected function provisionSSLLetsEncrypt(Domain $domain): void
    {
        // This would use the acme-php library
        // For production, install via: composer require acme-php/acme-php
        
        // Example (pseudo-code):
        // $acmeClient = new AcmeClient(...);
        // $certificate = $acmeClient->getCertificate($domain->domain);
        // $this->installCertificate($domain, $certificate);
        
        throw new \Exception('LetsEncrypt integration not yet implemented');
    }
}
