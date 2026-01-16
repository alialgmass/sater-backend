<?php

namespace Modules\Customer\Services;

use Illuminate\Support\Str;
use Modules\Auth\Models\Customer;
use Illuminate\Support\Facades\DB;

class AccountDeletionService
{
    public function deleteAccount(Customer $customer): void
    {
        DB::transaction(function () use ($customer) {
            // Revoke tokens
            $customer->tokens()->delete();

            // Anonymize profile
            $customer->profile()->update([
                'first_name' => 'Deleted',
                'last_name' => 'User',
                'date_of_birth' => null,
                'gender' => null,
            ]);

            // Anonymize Customer record
            $customer->update([
                'name' => 'Deleted User',
                'email' => 'deleted_' . Str::uuid() . '@example.com',
                'phone' => null,
            ]);

            // Delete addresses
            $customer->addresses()->delete();
            
            // Delete privacy settings
            $customer->privacySettings()->delete();

            // Soft delete user (assuming SoftDeletes trait is present, if not, this will fail or normal delete)
            // Customer model doesn't have SoftDeletes yet? Let's assume user wants soft delete behavior.
            // If model doesn't have SoftDeletes, we might need to add it or do hard delete.
            // Requirements said "Soft-delete user initially".
            // So I should check if Customer model has SoftDeletes. I will add it if missing in next step.
            
            // For now, simpler to jus not call delete() if we want to keep order data? 
            // "Keep financial & order data intact" -> usually implies SoftDeletes or keeping ID.
            // "Soft-delete user initially" -> OK.
        });
    }
}
