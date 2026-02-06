<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'email' => ['nullable', 'string', 'email'],
            'mobile' => ['nullable', 'string', 'digits:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Please provide a valid email address.',
            'mobile.digits' => 'Mobile number must be 10 digits.',
        ];
    }
}
