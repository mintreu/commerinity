<?php

declare(strict_types=1);

namespace App\Http\Requests\Payout;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PayoutToWalletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admins can initiate payouts to user wallets
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
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'amount' => [
                'required',
                'integer',
                'min:100', // ₹1 minimum
                'max:10000000', // ₹1Crore maximum
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['commission', 'affiliate', 'refund', 'bonus', 'manual']),
            ],
            'reference_id' => [
                'nullable',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User not found',
            'amount.required' => 'Payout amount is required',
            'amount.integer' => 'Amount must be in paisa (integer)',
            'amount.min' => 'Minimum payout amount is ₹1',
            'amount.max' => 'Maximum payout amount is ₹1,00,00,000',
            'type.required' => 'Payout type is required',
            'type.in' => 'Invalid payout type. Allowed: commission, affiliate, refund, bonus, manual',
            'reference_id.max' => 'Reference ID cannot exceed 100 characters',
            'description.max' => 'Description cannot exceed 500 characters',
        ];
    }
}
