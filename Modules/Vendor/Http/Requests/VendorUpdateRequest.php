<?php

namespace Modules\Vendor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Vendor\Enums\VendorStatus;

class VendorUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $vendorId = $this->route('vendor');
        return [



            'name' => [
                'sometimes',
                'string',
                'min:3',
                'max:255',
            ],
            'shop_name' => [
                'sometimes',
                'string',
                'min:3',
                'max:255',
            ],
            'shop_slug' => [
                'sometimes',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-z0-9\-]+$/',
                Rule::unique('vendors', 'shop_slug')
                    ->ignore($vendorId)
                    ->whereNull('deleted_at'),
            ],
            'whatsapp' => [
                'sometimes',
                'string',
                'min:10',
                'max:15',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:2048',
            ],
            'cover' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:5120',
            ],
            'status' => [
                'sometimes',
                'string',
                Rule::in(VendorStatus::values()),
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
