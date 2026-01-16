<?php

namespace Modules\Cart\Enums;

enum CartItemStatusEnum: string
{
    case AVAILABLE = 'available';
    case OUT_OF_STOCK = 'out_of_stock';
}
