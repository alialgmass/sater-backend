<?php

namespace App\Listeners\Payment;

use App\Events\Payment\PaymentSucceeded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendPaymentReceiptListener
{
    public function handle(PaymentSucceeded $event): void
    {
        $payment = $event->payment;
        
        // Log the payment success
        Log::info('Payment succeeded', [
            'payment_id' => $payment->id,
            'vendor_order_id' => $payment->vendor_order_id,
            'customer_id' => $payment->customer_id,
            'amount' => $payment->amount,
            'method' => $payment->method->value,
        ]);
        
        // Generate and send receipt
        $receiptService = app(\App\Services\Payment\ReceiptService::class);
        $receiptService->generateAndSend($payment);
    }
}