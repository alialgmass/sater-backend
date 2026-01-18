<?php

namespace App\Enums\Payment;

use Illuminate\Http\Resources\Json\JsonResource;

enum PaymentMethodEnum: string
{
    case CASH_ON_DELIVERY = 'cod';
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case WALLET = 'wallet';
    case BANK_TRANSFER = 'bank_transfer';
    case MOBILE_MONEY = 'mobile_money';

    public function label(): string
    {
        return match($this) {
            self::CASH_ON_DELIVERY => 'Cash on Delivery',
            self::CREDIT_CARD => 'Credit Card',
            self::DEBIT_CARD => 'Debit Card',
            self::WALLET => 'Digital Wallet',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::MOBILE_MONEY => 'Mobile Money',
        };
    }

    public function isOnline(): bool
    {
        return !in_array($this, [
            self::CASH_ON_DELIVERY,
        ]);
    }

    public function isCashOnDelivery(): bool
    {
        return $this === self::CASH_ON_DELIVERY;
    }
}