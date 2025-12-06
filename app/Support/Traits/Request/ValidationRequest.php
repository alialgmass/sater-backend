<?php

namespace App\Support\Traits\Request;

use App\Exceptions\ApiException\ValidationExceptionResponse;
use Illuminate\Contracts\Validation\Validator;

trait ValidationRequest
{
    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return array_merge([

        ], $this->attributesAction());
    }

    public function attributesAction(): array
    {
        return [];
    }

    protected function failedValidation(Validator $validator)
    {
        throw ValidationExceptionResponse::instance($validator->errors()->messages());
    }
}
