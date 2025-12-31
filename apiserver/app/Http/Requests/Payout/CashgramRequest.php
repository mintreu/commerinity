<?php

declare(strict_types=1);

namespace App\Http\Requests\Payout;

use Illuminate\Foundation\Http\FormRequest;

final class CashgramRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cashgram_id' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9-_]+$/',
            ],
            'amount' => [
                'required',
                'integer',
                'min:100', // ₹1 minimum (100 paisa)
                'max:100000000', // ₹10Lakh maximum
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/',
            ],
            'email' => [
                'nullable',
                'email',
                'max:100',
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],
            'purpose' => [
                'nullable',
                'string',
                'max:50',
            ],
            'remark' => [
                'nullable',
                'string',
                'max:100',
            ],
            'notify_customer' => [
                'nullable',
                'boolean',
            ],
            'expire_by' => [
                'nullable',
                'date',
                'after:now',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Cashgram amount is required',
            'amount.integer' => 'Amount must be in paisa (integer)',
            'amount.min' => 'Minimum cashgram amount is ₹1',
            'amount.max' => 'Maximum cashgram amount is ₹10,00,000',
            'phone.required' => 'Phone number is required',
            'phone.regex' => 'Please enter a valid 10-digit phone number',
            'email.email' => 'Please enter a valid email address',
            'name.required' => 'Recipient name is required',
            'name.min' => 'Name must be at least 2 characters',
            'purpose.max' => 'Purpose cannot exceed 50 characters',
            'expire_by.after' => 'Expiry date must be in the future',
        ];
    }
}
