<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Repositories\Contracts\DomainRepositoryInterface;
use App\Repositories\Contracts\TenantRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantRegistrationService
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly DomainRepositoryInterface $domainRepository,
        private readonly EmailVerificationService  $verificationService,
    ) {}

    /**
     * Register a new tenant and fire a verification email.
     *
     * @return array{tenant: Tenant, domain: \App\Models\Domain}
     * @throws \Throwable
     */
    public function register(array $data, string $fullSubdomain): array
    {
        return DB::transaction(function () use ($data, $fullSubdomain) {
            $tenant = $this->tenantRepository->create([
                'store_name'    => $data['store_name'],
                'email'         => $data['email'],
                'password_hash' => $data['password'],
                'language'      => $data['language'],
                'status'        => 'pending_email_verification',
            ]);

            $domain = $this->domainRepository->create([
                'domain'     => $fullSubdomain,
                'tenant_id'  => $tenant->id,
                'verified'   => false,
                'type'       => 'subdomain',
                'is_primary' => true,
            ]);

            $this->verificationService->createVerificationAndSend($tenant);

            Log::info('New tenant registered', [
                'tenant_id'  => $tenant->id,
                'store_name' => $tenant->store_name,
                'email'      => $tenant->email,
                'subdomain'  => $domain->domain,
            ]);

            return compact('tenant', 'domain');
        });
    }

    /**
     * Verify a token and return the activated tenant.
     *
     * @throws \RuntimeException when token is invalid/expired
     */
    public function verifyEmail(string $token): Tenant
    {
        $tenant = $this->verificationService->verifyToken($token);

        if (! $tenant) {
            throw new \RuntimeException('Invalid or expired verification token.', 404);
        }

        return $tenant;
    }

    /**
     * Resend the verification email for a pending tenant email.
     *
     * @throws \RuntimeException when already verified
     */
    public function resendVerification(string $email): void
    {
        $tenant = $this->tenantRepository->findByEmail($email);

        if ($tenant->status === 'active') {
            throw new \RuntimeException('Email is already verified.', 400);
        }

        $this->verificationService->resendVerification($tenant);
    }

    /**
     * Return all active subscription plans formatted for the API.
     */
    public function getActivePlans(): Collection
    {
        return SubscriptionPlan::active()
            ->ordered()
            ->get()
            ->map(fn (SubscriptionPlan $plan) => [
                'id'          => $plan->id,
                'name'        => $plan->name,
                'slug'        => $plan->slug,
                'description' => $plan->description,
                'price'       => [
                    'monthly'  => $plan->price_monthly,
                    'yearly'   => $plan->price_yearly,
                    'currency' => 'SAR',
                ],
                'features'   => $plan->features,
                'trial_days' => $plan->trial_days,
                'is_active'  => $plan->is_active,
            ]);
    }

    /**
     * Subscribe a tenant to a plan.
     *
     * @return array{subscription: \App\Models\Subscription, redirect_url: string}
     * @throws \RuntimeException when tenant or plan not found
     * @throws \Throwable
     */
    public function subscribe(string $tenantId, string $planId, string $billingCycle): array
    {
        $tenant = $this->tenantRepository->findById($tenantId);

        if (! $tenant) {
            throw new \RuntimeException('Tenant not found.', 404);
        }

        $plan = SubscriptionPlan::findOrFail($planId);

        return DB::transaction(function () use ($tenant, $plan, $billingCycle) {
            $subscription = $tenant->subscriptions()->create([
                'plan_id'       => $plan->id,
                'status'        => 'active',
                'billing_cycle' => $billingCycle,
                'starts_at'     => now(),
                'ends_at'       => null,
                'trial_ends_at' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
                'currency'      => 'SAR',
                'amount'        => $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly,
            ]);

            $this->tenantRepository->update($tenant, ['current_plan_id' => $plan->id]);

            Log::info('Tenant subscribed to plan', [
                'tenant_id' => $tenant->id,
                'plan_id'   => $plan->id,
                'plan_name' => $plan->name,
            ]);

            $domain = $this->domainRepository->getForTenant($tenant)->first();

            return [
                'subscription' => $subscription->load('plan'),
                'plan'         => $plan,
                'redirect_url' => "https://{$domain?->domain}/dashboard",
            ];
        });
    }
}
