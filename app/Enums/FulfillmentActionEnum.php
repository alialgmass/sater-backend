<?php

namespace App\Enums;

enum FulfillmentActionEnum: string
{
    case MARK_AS_SHIPPED = 'mark_as_shipped';
    case PRINT_PACKING_SLIPS = 'print_packing_slips';
    case CONFIRM_COD = 'confirm_cod';
    case CANCEL_ORDER = 'cancel_order';
}