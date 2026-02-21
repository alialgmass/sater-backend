<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Email Verification Model
 * 
 * Stores email verification tokens for tenant registration.
 * Each record represents a pending email verification for a tenant.
 * 
 * @extends Model
 */
class EmailVerification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'token',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the verification record.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if the verification token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Scope a query to only valid (non-expired) verifications.
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Generate a new verification token.
     * 
     * @return string The generated token
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Create a verification record for a tenant.
     * 
     * @param Tenant $tenant The tenant to verify
     * @param int $validityHours Hours until expiration (default: 24)
     * @return EmailVerification
     */
    public static function createForTenant(Tenant $tenant, int $validityHours = 24): self
    {
        return static::create([
            'tenant_id' => $tenant->id,
            'token' => self::generateToken(),
            'expires_at' => now()->addHours($validityHours),
        ]);
    }
}
