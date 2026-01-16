<?php

namespace Modules\Checkout\Interfaces;

use Modules\Order\Models\Order;
use Modules\Checkout\Models\PaymentTransaction;

interface PaymentGatewayInterface
{
    public function initiatePayment(Order $order, float $amount): PaymentTransaction;
    
    public function processPayment(PaymentTransaction $transaction): bool;
    
    public function refundPayment(PaymentTransaction $transaction): bool;
    
    public function verifyPayment(string $transactionId): bool;
}
