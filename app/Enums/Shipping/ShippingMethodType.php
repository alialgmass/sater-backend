<?php

namespace App\Enums\Shipping;

enum ShippingMethodType: string
{
    case STANDARD = 'standard';
    case EXPRESS = 'express';
    case COD = 'cod';
}