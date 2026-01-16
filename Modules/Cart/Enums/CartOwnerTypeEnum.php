<?php

namespace Modules\Cart\Enums;

enum CartOwnerTypeEnum: string
{
    case GUEST = 'guest';
    case CUSTOMER = 'customer';
}
