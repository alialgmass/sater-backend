<?php

namespace Modules\Order\Enums;

enum VendorOrderStatusEnum: string
{
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case PACKED = 'packed';
    case SHIPPED = 'shipped';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}
