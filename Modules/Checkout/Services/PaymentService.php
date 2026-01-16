<?php

namespace Modules\Checkout\Services;

use Illuminate\Support\Str;
use Modules\Order\Models\Order;
use Modules\Checkout\Models\PaymentTransaction;
use Modules\Checkout\Interfaces\PaymentGatewayInterface;

class PaymentService
{
    public function __construct(
        protected ?PaymentGatewayInterface $gateway = null
    ) {}

    public function initiatePayment(Order $order): PaymentTransaction
    {
        $transaction = PaymentTransaction::create([
            'vendor_order_id' => $order->id,
            'transaction_id' => $this->generateTransactionId(),
            'payment_method' => $order->payment_method,
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        if ($order->payment_method === 'online' && $this->gateway) {
            $this->gateway->initiatePayment($order, $order->total_amount);
        }

        return $transaction;
    }

    public function processPayment(PaymentTransaction $transaction): bool
    {
        if ($transaction->payment_method->value === 'cod') {
            // COD doesn't need immediate processing
            $transaction->update(['status' => 'pending']);
            return true;
        }

        if ($this->gateway) {
            $success = $this->gateway->processPayment($transaction);
            $transaction->update([
                'status' => $success ? 'completed' : 'failed'
            ]);
            return $success;
        }

        return false;
    }

    public function refundPayment(PaymentTransaction $transaction): bool
    {
        if ($this->gateway) {
            $success = $this->gateway->refundPayment($transaction);
            if ($success) {
                $transaction->update(['status' => 'refunded']);
            }
            return $success;
        }

        return false;
    }

    protected function generateTransactionId(): string
    {
        return 'TXN-' . strtoupper(Str::random(16));
    }
}
