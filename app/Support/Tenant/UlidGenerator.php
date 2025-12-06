<?php

namespace App\Support\Tenant;

use Symfony\Component\Uid\Ulid;

class UlidGenerator extends \Stancl\Tenancy\UUIDGenerator
{
    public static function generate($resource): string
    {
        return Ulid::generate();
    }
}
