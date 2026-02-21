<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tenant Subscription Middleware
 * 
 * Ensures that tenant accounts are in good standing before allowing access.
 * Blocks suspended and cancelled tenants from accessing their store.
 */
class TenantSubscriptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->tenant;
        
        if (!$tenant) {
            return redirect()->route('home');
        }
        
        // Handle suspended tenants
        if ($tenant->isSuspended()) {
            return $this->handleSuspendedTenant($request, $tenant);
        }
        
        // Handle cancelled tenants (grace period for data export)
        if ($tenant->isCancelled()) {
            return $this->handleCancelledTenant($request, $tenant);
        }
        
        // Handle pending verification
        if ($tenant->isPendingVerification()) {
            return $this->handlePendingVerification($request, $tenant);
        }
        
        // Tenant is active, allow request
        return $next($request);
    }
    
    /**
     * Handle suspended tenant requests.
     */
    protected function handleSuspendedTenant(Request $request, $tenant): Response
    {
        // Allow tenant admin to see suspension page
        if ($request->routeIs('tenant.suspended')) {
            return $next($request);
        }
        
        // Block all other routes
        return response()->view('errors.suspended', [
            'tenant' => $tenant,
            'reason' => $tenant->suspension_reason ?? 'Account suspended',
        ], 403);
    }
    
    /**
     * Handle cancelled tenant requests.
     */
    protected function handleCancelledTenant(Request $request, $tenant): Response
    {
        // Allow data export routes during grace period
        if ($request->routeIs('tenant.data-export.*')) {
            return $next($request);
        }
        
        // Allow cancellation info page
        if ($request->routeIs('tenant.cancellation')) {
            return $next($request);
        }
        
        // Redirect all other routes to data export page
        return redirect()->route('tenant.data-export.index')
            ->with('info', 'Your account is cancelled. You can export your data until ' . 
                $tenant->deletion_scheduled_at?->format('Y-m-d'));
    }
    
    /**
     * Handle pending verification requests.
     */
    protected function handlePendingVerification(Request $request, $tenant): Response
    {
        // Allow verification routes
        if ($request->routeIs('tenant.verify.*') || $request->routeIs('tenant.resend-verification')) {
            return $next($request);
        }
        
        // Redirect to verification pending page
        return redirect()->route('tenant.verification-pending')
            ->with('email', $tenant->email);
    }
}
