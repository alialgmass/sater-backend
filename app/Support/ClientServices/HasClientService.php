<?php

namespace App\Support\ClientServices;

/**
 * @property string $serviceClass
 */
trait HasClientService
{
    public function service(): ClientService
    {
        return new $this->serviceClass($this);
    }
}
