<?php

namespace Modules\Checkout\Enums;

enum PaymentMethodEnum: string
{
    case COD = 'cod';
    case ONLINE = 'online';
}
