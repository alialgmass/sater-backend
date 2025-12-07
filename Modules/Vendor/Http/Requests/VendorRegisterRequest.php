<?php

namespace Modules\Vendor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;


class VendorRegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'phone' => [
                'required',
                'string',
                'min:10',
                'max:15',
                Rule::unique('vendors', 'phone')->whereNull('deleted_at'),
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(6)
                    ->letters()
                    ->numbers(),
            ],
            'shop_name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'shop_slug' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-z0-9\-]+$/',
                Rule::unique('vendors', 'shop_slug')->whereNull('deleted_at'),
            ],
            'whatsapp' => [
                'required',
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
                'max:2048', // 2MB
            ],
            'cover' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:5120', // 5MB
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Please provide your full name.',
            'name.min' => 'Name must be at least 3 characters.',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number is already registered.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 6 characters.',
            'shop_name.required' => 'Shop name is required.',
            'shop_name.min' => 'Shop name must be at least 3 characters.',
            'shop_slug.required' => 'Shop URL slug is required.',
            'shop_slug.unique' => 'This shop URL is already taken.',
            'shop_slug.regex' => 'Shop slug can only contain lowercase letters, numbers, and hyphens.',
            'whatsapp.required' => 'WhatsApp number is required.',
            'logo.image' => 'Logo must be an image file.',
            'logo.mimes' => 'Logo must be a JPEG, PNG, GIF, or WebP file.',
            'logo.max' => 'Logo size cannot exceed 2MB.',
            'cover.image' => 'Cover must be an image file.',
            'cover.mimes' => 'Cover must be a JPEG, PNG, GIF, or WebP file.',
            'cover.max' => 'Cover size cannot exceed 5MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'shop_name' => 'shop name',
            'shop_slug' => 'shop URL slug',
            'whatsapp' => 'WhatsApp number',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize phone numbers
        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/[\s\-\(\)]/', '', $this->phone),
            ]);
        }

        if ($this->has('whatsapp')) {
            $this->merge([
                'whatsapp' => preg_replace('/[\s\-\(\)]/', '', $this->whatsapp),
            ]);
        }

        // Normalize shop slug
        if ($this->has('shop_slug')) {
            $this->merge([
                'shop_slug' => strtolower($this->shop_slug),
            ]);
        }
    }
}
