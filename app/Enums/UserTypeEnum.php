<?php

namespace App\Enums;

use App\Support\Traits\EnumCommonTrait;

enum UserTypeEnum: string
{
    use EnumCommonTrait;

    case user = 'user';
    case system = 'system';

}
