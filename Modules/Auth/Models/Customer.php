<?php

namespace Modules\Auth\Models;

use Modules\Customer\Models\Customer as CustomerEloquent;

class Customer extends CustomerEloquent
{
    public function isVerified(): bool
    {
        return (bool)$this->phone_verified_at;
    }
}
