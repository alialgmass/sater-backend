<?php

namespace Modules\Auth\DTOs;

use Modules\Auth\Http\Requests\VerifyOtpRequest;

class VerifyOtpData
{
    public function __construct(
        public string $otp,
    ){}
    public static function fromRequest(VerifyOtpRequest $request):static
    {
       return new self($request->otp);
    }
}
