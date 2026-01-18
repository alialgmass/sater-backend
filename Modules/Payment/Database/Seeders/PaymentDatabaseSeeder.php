<?php

namespace Modules\Payment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Payment\Models\Payment;
use Modules\Payment\Models\VendorPayment;
use Modules\Payment\Enums\PaymentMethodEnum;
use Modules\Payment\Enums\PaymentStatusEnum;
use Modules\Payment\Enums\GatewayEnum;

class PaymentDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample payments
        $samplePayments = [
            [
                'customer_id' => 1,
                'vendor_order_id' => 1,
                'amount' => 150.00,
                'currency' => 'USD',
                'method' => PaymentMethodEnum::CREDIT_CARD,
                'gateway' => GatewayEnum::STRIPE,
                'status' => PaymentStatusEnum::COMPLETED,
                'transaction_id' => 'txn_' . uniqid(),
                'reference_id' => 'ref_' . uniqid(),
                'paid_at' => now(),
            ],
            [
                'customer_id' => 2,
                'vendor_order_id' => 2,
                'amount' => 75.50,
                'currency' => 'USD',
                'method' => PaymentMethodEnum::CASH_ON_DELIVERY,
                'gateway' => null,
                'status' => PaymentStatusEnum::PENDING,
                'transaction_id' => 'cod_' . uniqid(),
                'reference_id' => 'cod_ref_' . uniqid(),
            ],
            [
                'customer_id' => 3,
                'vendor_order_id' => 3,
                'amount' => 200.00,
                'currency' => 'EGP',
                'method' => PaymentMethodEnum::WALLET,
                'gateway' => GatewayEnum::FAWRY,
                'status' => PaymentStatusEnum::FAILED,
                'transaction_id' => 'fawry_' . uniqid(),
                'reference_id' => 'fawry_ref_' . uniqid(),
            ],
        ];

        foreach ($samplePayments as $paymentData) {
            Payment::create($paymentData);
        }

        // Create sample vendor payments
        $sampleVendorPayments = [
            [
                'vendor_order_id' => 1,
                'total_amount' => 150.00,
                'payment_status' => PaymentStatusEnum::COMPLETED,
                'paid_at' => now(),
            ],
            [
                'vendor_order_id' => 2,
                'total_amount' => 75.50,
                'payment_status' => PaymentStatusEnum::PENDING,
            ],
            [
                'vendor_order_id' => 3,
                'total_amount' => 200.00,
                'payment_status' => PaymentStatusEnum::FAILED,
            ],
        ];

        foreach ($sampleVendorPayments as $vendorPaymentData) {
            VendorPayment::create($vendorPaymentData);
        }
    }
}