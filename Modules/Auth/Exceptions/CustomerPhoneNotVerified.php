<?php

namespace Modules\Auth\Exceptions;

use App\Exceptions\ApiException\ApiException;
use App\Exceptions\ApiException\ExceptionResponse;
use Exception;

class CustomerPhoneNotVerified extends ExceptionResponse {
    protected int $customCode =4010;

}
