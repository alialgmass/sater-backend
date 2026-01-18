<?php

namespace Modules\Payment\Services;

use Modules\Payment\Enums\GatewayEnum;
use Modules\Payment\Interfaces\PaymentGatewayInterface;
use Modules\Payment\Gateways\StripeGateway;
use Modules\Payment\Gateways\PaymobGateway;
use Modules\Payment\Gateways\FawryGateway;
use Modules\Payment\Gateways\STCPayGateway;

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