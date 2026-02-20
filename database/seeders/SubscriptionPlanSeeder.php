<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

/**
 * Subscription Plan Seeder
 * 
 * Seeds the initial subscription plans for the platform.
 * Plans: Starter (Free), Professional, Enterprise
 */
class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for small stores getting started',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'features' => [
                    'products_limit' => 50,
                    'storage_gb' => 2,
                    'users_limit' => 2,
                    'custom_domain' => false,
                    'analytics' => 'basic',
                    'support_level' => 'email',
                    'api_rate_limit' => 100,
                ],
                'trial_days' => 14,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For growing businesses with advanced needs',
                'price_monthly' => 299,
                'price_yearly' => 2990, // 2 months free
                'features' => [
                    'products_limit' => 1000,
                    'storage_gb' => 20,
                    'users_limit' => 10,
                    'custom_domain' => true,
                    'analytics' => 'advanced',
                    'support_level' => 'priority',
                    'api_rate_limit' => 1000,
                ],
                'trial_days' => 14,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large-scale operations with unlimited access',
                'price_monthly' => 999,
                'price_yearly' => 9990, // 2 months free
                'features' => [
                    'products_limit' => -1, // unlimited
                    'storage_gb' => 100,
                    'users_limit' => -1, // unlimited
                    'custom_domain' => true,
                    'analytics' => 'advanced',
                    'support_level' => 'dedicated',
                    'api_rate_limit' => 10000,
                ],
                'trial_days' => 30,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }

        $this->command->info('Subscription plans seeded successfully!');
    }
}
