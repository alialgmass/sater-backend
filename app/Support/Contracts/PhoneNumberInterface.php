<?php

namespace App\Support\Contracts;

interface PhoneNumberInterface
{
    public function phoneUtilityObject(): object;

    public function phoneUtility(): ?\libphonenumber\PhoneNumber;

    public function getNationalNumber(): ?string;
}
