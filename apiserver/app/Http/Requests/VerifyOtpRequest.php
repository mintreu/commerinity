<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:mobile,email'],
            'value' => ['required', 'string', function ($attribute, $value, $fail) {
                if ($this->input('type') === 'mobile' && ! preg_match('/^\d{10}$/', (string) $value)) {
                    $fail('The mobile number must be 10 digits.');
                }
                if ($this->input('type') === 'email' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $fail('The value must be a valid email address.');
                }
            }],
            'otp' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Please specify whether verifying mobile or email OTP.',
            'type.in' => 'Type must be either mobile or email.',
            'value.required' => 'Please provide your mobile number or email address.',
            'otp.required' => 'Please enter the OTP code.',
            'otp.size' => 'OTP must be exactly 6 digits.',
            'otp.regex' => 'OTP must contain only numbers.',
        ];
    }
}
