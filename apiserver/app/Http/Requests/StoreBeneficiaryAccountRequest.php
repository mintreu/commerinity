<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Casts\BeneficiaryTypeCast;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBeneficiaryAccountRequest extends FormRequest
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
        $type = $this->input('type');
        $isBank = in_array($type, ['savings', 'current']);

        return [
            'type' => [
                'required',
                'string',
                Rule::in(array_column(BeneficiaryTypeCast::cases(), 'value')),
            ],
            'holder_name' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            // Bank account fields (required for savings/current)
            'account_number' => [
                Rule::requiredIf($isBank),
                'nullable',
                'string',
                'min:9',
                'max:18',
                'regex:/^[0-9]+$/',
            ],
            'confirm_account_number' => [
                Rule::requiredIf($isBank),
                'nullable',
                'same:account_number',
            ],
            'ifsc_code' => [
                Rule::requiredIf($isBank),
                'nullable',
                'string',
                'size:11',
                'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i',
            ],
            'bank_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'branch_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            // UPI field (required for UPI type)
            'upi_id' => [
                Rule::requiredIf($type === 'upi'),
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9.\-_]+@[a-zA-Z]+$/i',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Please select an account type',
            'type.in' => 'Invalid account type selected',
            'holder_name.required' => 'Account holder name is required',
            'holder_name.regex' => 'Holder name should contain only letters and spaces',
            'account_number.required' => 'Account number is required for bank accounts',
            'account_number.min' => 'Account number must be at least 9 digits',
            'account_number.max' => 'Account number cannot exceed 18 digits',
            'account_number.regex' => 'Account number should contain only digits',
            'confirm_account_number.same' => 'Account numbers do not match',
            'ifsc_code.required' => 'IFSC code is required for bank accounts',
            'ifsc_code.size' => 'IFSC code must be exactly 11 characters',
            'ifsc_code.regex' => 'Invalid IFSC code format (e.g., HDFC0001234)',
            'upi_id.required' => 'UPI ID is required for UPI accounts',
            'upi_id.regex' => 'Invalid UPI ID format (e.g., name@upi)',
        ];
    }
}
