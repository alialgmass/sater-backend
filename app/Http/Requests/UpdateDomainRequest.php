<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Update Domain Request
 * 
 * Validation rules for domain management operations.
 */
class UpdateDomainRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/',
                Rule::unique('domains', 'domain'),
            ],
            'is_primary' => [
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'domain.required' => 'Domain name is required.',
            'domain.regex' => 'Please enter a valid domain name (e.g., example.com).',
            'domain.unique' => 'This domain is already registered.',
            'is_primary.boolean' => 'Primary flag must be true or false.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'domain' => 'domain name',
            'is_primary' => 'primary domain flag',
        ];
    }
}
