<?php

namespace App\Services;

use App\Models\EmailVerification;
use App\Models\Tenant;
use App\Notifications\TenantRegistrationMail;
use Illuminate\Support\Facades\Notification;

/**
 * Email Verification Service
 * 
 * Handles email verification token generation, sending verification emails,
 * and token validation for tenant registration.
 */
class EmailVerificationService
{
    /**
     * Create email verification for a tenant and send verification email.
     * 
     * @param Tenant $tenant The tenant to verify
     * @return EmailVerification The created verification record
     */
    public function createVerificationAndSend(Tenant $tenant): EmailVerification
    {
        // Delete any existing verifications for this tenant
        EmailVerification::where('tenant_id', $tenant->id)->delete();
        
        // Create new verification
        $verification = EmailVerification::createForTenant($tenant, 24);
        
        // Send verification email
        $this->sendVerificationEmail($tenant, $verification);
        
        return $verification;
    }
    
    /**
     * Send verification email to tenant.
     * 
     * @param Tenant $tenant
     * @param EmailVerification $verification
     * @return void
     */
    protected function sendVerificationEmail(Tenant $tenant, EmailVerification $verification): void
    {
        $verificationUrl = $this->getVerificationUrl($verification);
        
        Notification::route('mail', $tenant->email)
            ->notify(new TenantRegistrationMail($tenant, $verificationUrl));
    }
    
    /**
     * Get the verification URL for a tenant.
     * 
     * @param EmailVerification $verification
     * @return string The verification URL
     */
    protected function getVerificationUrl(EmailVerification $verification): string
    {
        // For now, use central domain. In production, this would be the platform domain.
        $baseUrl = config('app.url');
        
        return "{$baseUrl}/tenants/verify/{$verification->token}";
    }
    
    /**
     * Verify a token and activate the tenant.
     * 
     * @param string $token The verification token
     * @return Tenant|null The verified tenant or null if verification failed
     */
    public function verifyToken(string $token): ?Tenant
    {
        $verification = EmailVerification::where('token', $token)
            ->valid()
            ->first();
        
        if (!$verification) {
            return null;
        }
        
        $tenant = $verification->tenant;
        
        // Activate tenant
        $tenant->update([
            'status' => 'active',
        ]);
        
        // Create default admin user for the tenant
        $this->createTenantAdminUser($tenant);
        
        // Delete verification record
        $verification->delete();
        
        return $tenant;
    }
    
    /**
     * Create default admin user for a newly verified tenant.
     * 
     * @param Tenant $tenant
     * @return void
     */
    protected function createTenantAdminUser(Tenant $tenant): void
    {
        // Note: This creates a user in the tenant's database
        // We need to run this in tenant context
        
        // For now, we'll handle this in the controller after switching to tenant context
        // This is a placeholder for the logic
    }
    
    /**
     * Resend verification email.
     * 
     * @param Tenant $tenant
     * @return EmailVerification The new verification record
     */
    public function resendVerification(Tenant $tenant): EmailVerification
    {
        return $this->createVerificationAndSend($tenant);
    }
    
    /**
     * Check if a tenant has a valid (non-expired) verification.
     * 
     * @param Tenant $tenant
     * @return bool
     */
    public function hasValidVerification(Tenant $tenant): bool
    {
        return EmailVerification::where('tenant_id', $tenant->id)
            ->valid()
            ->exists();
    }
}
