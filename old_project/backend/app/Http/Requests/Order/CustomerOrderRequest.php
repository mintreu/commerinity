<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class CustomerOrderRequest extends FormRequest
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
            'gift' => 'required|boolean',
            'payment_provider' => 'required|string|max:100',
            'billing_is_shipping' => 'required|boolean',
            'billing_address' => 'nullable|string|exists:addresses,uuid|required_with:shipping_address',
            'shipping_address' => 'nullable|string|exists:addresses,uuid|required_with:billing_address',

        ];
    }
}
