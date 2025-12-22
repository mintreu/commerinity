<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class GuestOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
            'address_1' => 'required|string',
            'city' => 'required|string',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'customer_mobile' => 'required|string|regex:/^[0-9]{10}$/',
//                    'district' => 'required|string|exists:blocks,district_names',
            'landmark' => 'required|string',
            'postal_code' => 'required|string|regex:/^[0-9]{6}$/',
            'state' => 'required|string|exists:states,code',
            'block_id'  => 'required|string|exists:blocks,name',
        ];
    }
}
