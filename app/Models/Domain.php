<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

/**
 * Domain Model
 * 
 * Represents a domain associated with a tenant.
 * Supports both subdomains (e.g., store.sater.com) and custom domains (e.g., www.mystore.com).
 * 
 * @extends BaseDomain
 */
class Domain extends BaseDomain
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'domain',
        'tenant_id',
        'verified',
        'verified_at',
        'verification_token',
        'ssl_status',
        'ssl_expires_at',
        'is_primary',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified' => 'boolean',
        'verified_at' => 'datetime',
        'ssl_expires_at' => 'datetime',
        'is_primary' => 'boolean',
    ];

    /**
     * Verify domain ownership via DNS TXT record.
     * 
     * Queries DNS for the expected TXT record and marks domain as verified if found.
     * 
     * @return bool True if verification succeeded, false otherwise
     */
    public function verify(): bool
    {
        $expectedValue = "sater-verify={$this->verification_token}";
        
        // Query DNS TXT records
        $records = @dns_get_record($this->domain, DNS_TXT);
        
        if ($records === false) {
            return false;
        }
        
        foreach ($records as $record) {
            if (isset($record['txt']) && strpos($record['txt'], 'sater-verify=') === 0) {
                if ($record['txt'] === $expectedValue) {
                    $this->update([
                        'verified' => true,
                        'verified_at' => now(),
                    ]);
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Generate a new verification token for DNS verification.
     * 
     * @return string The generated verification token
     */
    public function generateVerificationToken(): string
    {
        $token = bin2hex(random_bytes(16));
        
        $this->update([
            'verification_token' => $token,
        ]);
        
        return $token;
    }

    /**
     * Get DNS verification instructions for this domain.
     * 
     * @return array<string, string> DNS record details
     */
    public function getVerificationInstructions(): array
    {
        if (!$this->verification_token) {
            $this->generateVerificationToken();
        }
        
        return [
            'type' => 'TXT',
            'name' => "_sater-verification.{$this->domain}",
            'value' => "sater-verify={$this->verification_token}",
        ];
    }

    /**
     * Scope a query to only verified domains.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Scope a query to only primary domains.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope a query to only custom domains (not subdomains).
     */
    public function scopeCustom($query)
    {
        return $query->where('type', 'custom');
    }

    /**
     * Check if this is a subdomain.
     */
    public function isSubdomain(): bool
    {
        return str_ends_with($this->domain, '.sater.com');
    }

    /**
     * Check if this is a custom domain.
     */
    public function isCustom(): bool
    {
        return !$this->isSubdomain();
    }
}
