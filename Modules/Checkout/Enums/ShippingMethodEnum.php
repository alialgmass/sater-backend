<?php

namespace Modules\Checkout\Enums;

enum ShippingMethodEnum: string
{
    case STANDARD = 'standard';
    case EXPRESS = 'express';
}
