<?php

namespace App\Services\Payment;

use App\Enums\Payment\GatewayEnum;
use App\Interfaces\Payment\PaymentGatewayInterface;
use App\Gateways\Payment\StripeGateway;
use App\Gateways\Payment\PaymobGateway;
use App\Gateways\Payment\FawryGateway;
use App\Gateways\Payment\STCPayGateway;

class PaymentGatewayFactory
{
    public function getGateway(?GatewayEnum $gateway): PaymentGatewayInterface
    {
        if (!$gateway) {
            throw new \InvalidArgumentException('Gateway must be specified for online payments');
        }

        return match($gateway) {
            GatewayEnum::STRIPE => app(StripeGateway::class),
            GatewayEnum::PAYMOB => app(PaymobGateway::class),
            GatewayEnum::FAWRY => app(FawryGateway::class),
            GatewayEnum::STC_PAY => app(STCPayGateway::class),
            default => throw new \InvalidArgumentException("Unsupported gateway: {$gateway->value}"),
        };
    }
}