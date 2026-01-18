<?php

namespace App\Enums\Payment;

enum GatewayEnum: string
{
    case STRIPE = 'stripe';
    case PAYMOB = 'paymob';
    case FAWRY = 'fawry';
    case STC_PAY = 'stc_pay';
    case HYPER_PAY = 'hyper_pay';
    case LOCAL_BANK = 'local_bank';

    public function label(): string
    {
        return match($this) {
            self::STRIPE => 'Stripe',
            self::PAYMOB => 'Paymob',
            self::FAWRY => 'Fawry',
            self::STC_PAY => 'STC Pay',
            self::HYPER_PAY => 'Hyper Pay',
            self::LOCAL_BANK => 'Local Bank',
        };
    }

    public function supportsCod(): bool
    {
        return false; // Gateways don't support COD
    }

    public function supportsCards(): bool
    {
        return in_array($this, [
            self::STRIPE,
            self::PAYMOB,
            self::HYPER_PAY,
        ]);
    }

    public function supportsWallets(): bool
    {
        return in_array($this, [
            self::PAYMOB,
            self::FAWRY,
            self::STC_PAY,
        ]);
    }
}