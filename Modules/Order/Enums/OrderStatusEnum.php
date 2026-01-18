<?php

namespace Modules\Order\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case PARTIALLY_SHIPPED = 'partially_shipped';

}
