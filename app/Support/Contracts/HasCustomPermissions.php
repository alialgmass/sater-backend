<?php

namespace App\Support\Contracts;

interface HasCustomPermissions
{
    public function permissionsArray(): array;
}
