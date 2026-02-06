<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for email-based registration.
 *
 * Used when signup mode is 'email' instead of 'mobile'.
 */
final class EmailRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'otp' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'mobile' => [
                'nullable',
                'string',
                'digits:10',
                'unique:users,mobile',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referral_code' => ['nullable', 'string', 'size:8', 'exists:users,referral_code'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'otp.required' => 'OTP is required.',
            'otp.size' => 'OTP must be exactly 6 digits.',
            'otp.regex' => 'OTP must contain only numbers.',
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'mobile.digits' => 'Mobile number must be 10 digits.',
            'mobile.unique' => 'This mobile number is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'referral_code.exists' => 'Invalid referral code.',
        ];
    }
}
