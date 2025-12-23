<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for business inquiry contact form submissions.
 */
final class StoreBusinessInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:50'],
            'company_name' => ['required', 'string', 'max:190'],
            'address' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'email.required' => 'Please enter your business email.',
            'email.email' => 'Please enter a valid email address.',
            'phone.required' => 'Please enter your phone number.',
            'company_name.required' => 'Please enter your company name.',
            'address.required' => 'Please enter your company address.',
            'website.url' => 'Please enter a valid website URL (https://...).',
            'message.required' => 'Please describe your business inquiry.',
            'message.min' => 'Your message must be at least 10 characters.',
            'message.max' => 'Your message cannot exceed 5000 characters.',
        ];
    }
}
