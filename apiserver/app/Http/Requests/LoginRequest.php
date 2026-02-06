<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'email' => ['required_without:mobile', 'nullable', 'string', 'email'],
            'mobile' => ['required_without:email', 'nullable', 'string', 'digits:10'],
            'password' => ['required_without:otp', 'nullable', 'string'],
            'otp' => ['required_without:password', 'nullable', 'string', 'size:6', 'regex:/^\d{6}$/'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Either email or mobile is required.',
            'mobile.required_without' => 'Either mobile or email is required.',
            'mobile.digits' => 'Mobile number must be 10 digits.',
            'password.required_without' => 'Either password or OTP is required.',
            'otp.required_without' => 'Either OTP or password is required.',
            'otp.size' => 'OTP must be exactly 6 digits.',
            'otp.regex' => 'OTP must contain only numbers.',
        ];
    }
}
