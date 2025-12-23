<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SendOtpRequest extends FormRequest
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
                if (trim($value) === '') {
                    $fail('The '.$attribute.' cannot be empty.');
                }

                if ($this->input('type') === 'mobile') {
                    // E.164 format validation: +[country][number]
                    if (! preg_match('/^\+[1-9]\d{1,14}$/', $value)) {
                        $fail('The mobile number must be in valid E.164 format (e.g., +919876543210).');
                    }
                } elseif ($this->input('type') === 'email') {
                    if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('The value must be a valid email address.');
                    }
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Please specify whether sending OTP to mobile or email.',
            'type.in' => 'Type must be either mobile or email.',
            'value.required' => 'Please provide your mobile number or email address.',
        ];
    }
}
