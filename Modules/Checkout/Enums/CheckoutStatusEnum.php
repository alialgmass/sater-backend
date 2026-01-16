<?php

namespace Modules\Checkout\Enums;

enum CheckoutStatusEnum: string
{
    case PENDING = 'pending';
    case ADDRESS_SELECTED = 'address_selected';
    case SHIPPING_SELECTED = 'shipping_selected';
    case PAYMENT_SELECTED = 'payment_selected';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';
}
