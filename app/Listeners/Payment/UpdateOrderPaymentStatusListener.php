<?php

namespace App\Listeners\Payment;

use App\Events\Payment\PaymentInitiated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateOrderPaymentStatusListener
{
    public function handle(PaymentInitiated $event): void
    {
        $payment = $event->payment;
        
        // Log the payment initiation
        Log::info('Payment initiated', [
            'payment_id' => $payment->id,
            'vendor_order_id' => $payment->vendor_order_id,
            'customer_id' => $payment->customer_id,
            'amount' => $payment->amount,
            'method' => $payment->method->value,
            'status' => $payment->status->value,
        ]);
        
        // Update the vendor order payment status
        if ($payment->vendorOrder) {
            $payment->vendorOrder->update([
                'payment_status' => $payment->status->value,
            ]);
        }
    }
}