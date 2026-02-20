<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Register Tenant Request
 * 
 * Validation rules for tenant registration.
 */
class RegisterTenantRequest extends FormRequest
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
            'store_name' => [
                'required',
                'string',
                'min:3',
                'max:100',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('tenants', 'email'),
            ],
            'password' => [
                'required',
                'min:8',
                'regex:/[A-Z]/',      // At least one uppercase
                'regex:/[a-z]/',      // At least one lowercase
                'regex:/[0-9]/',      // At least one number
                'regex:/[^a-zA-Z0-9]/', // At least one special character
            ],
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9]+([\-][a-z0-9]+)*$/',
                Rule::unique('domains', 'domain')->where(function ($query) {
                    return $query->where('domain', 'like', '%.sater.com');
                }),
                Rule::notIn($this->reservedSubdomains()),
            ],
            'language' => [
                'required',
                'in:ar,en',
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
            'store_name.required' => 'Store name is required.',
            'store_name.min' => 'Store name must be at least 3 characters.',
            'email.unique' => 'This email is already registered.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
            'subdomain.required' => 'Subdomain is required.',
            'subdomain.unique' => 'This subdomain is already taken.',
            'subdomain.regex' => 'Subdomain can only contain lowercase letters, numbers, and hyphens.',
            'subdomain.not_in' => 'This subdomain is reserved.',
            'language.in' => 'Language must be either Arabic (ar) or English (en).',
        ];
    }

    /**
     * Get list of reserved subdomains.
     *
     * @return array<string>
     */
    protected function reservedSubdomains(): array
    {
        return [
            'www', 'mail', 'admin', 'api', 'app', 'blog', 'shop', 
            'store', 'support', 'help', 'docs', 'dev', 'staging', 
            'prod', 'test', 'demo', 'm', 'mobile', 'static', 'cdn', 
            'assets', 'dashboard', 'portal', 'login', 'register',
        ];
    }

    /**
     * Get subdomain with platform suffix.
     *
     * @return string
     */
    public function getFullSubdomain(): string
    {
        return $this->input('subdomain') . '.sater.com';
    }
}
