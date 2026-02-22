<?php

namespace Modules\Checkout\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cart_key'        => ['nullable', 'uuid'],
            'address'         => ['required', 'array'],
            'address.country' => ['required', 'string'],
            'address.city'    => ['required', 'string'],
            'address.street'  => ['required', 'string'],
            'shipping_method' => ['required', 'in:standard,express'],
            'payment_method'  => ['required', 'in:cod,online'],
            'coupon_code'     => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'address.country' => 'country',
            'address.city'    => 'city',
            'address.street'  => 'street',
        ];
    }
}
