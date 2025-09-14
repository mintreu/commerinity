<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PlaceOrderRequest extends FormRequest
{
    protected bool $isAuthenticated = false;

    protected function prepareForValidation(): void
    {
        // Cache auth state once for consistent conditional rules
        $this->isAuthenticated = auth('sanctum')->check();
        $this->merge([
            '_is_authenticated' => $this->isAuthenticated,
        ]);
    }

    public function authorize(): bool
    {
        // Allow both auth and guests; reject guests missing headers
        if (!$this->isAuthenticated) {
            if (!$this->hasHeader('x-guest-id') || !$this->hasHeader('x-guest-token')) {
                return false;
            }
        }
        return true;
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Unauthorized order attempt. Missing or invalid credentials.',
        ], 403));
    }

    public function rules(): array
    {
        // Shared fields
        $rules = [
            'gift'                => ['required','boolean'],
            'payment_provider'    => ['required','string','max:100'],
            'billing_is_shipping' => ['required','boolean'],
        ];

        if ($this->isAuthenticated) {
            // Authenticated: billing_address required, shipping_address required if not billing_is_shipping
            $rules += [
                'billing_address'  => [
                    Rule::requiredIf(fn () => $this->boolean('billing_is_shipping') || !$this->boolean('billing_is_shipping')),
                    'string','exists:addresses,uuid',
                ],
                'shipping_address' => [
                    Rule::requiredIf(fn () => !$this->boolean('billing_is_shipping')),
                    'string','exists:addresses,uuid',
                ],
            ];
        } else {
            // Guest: do NOT mention billing_address/shipping_address at all
            $rules += [
                'address_1'       => ['required','string'],
                'city'            => ['required','string'],
                'customer_name'   => ['required','string'],
                'customer_email'  => ['required','email'],
                'customer_mobile' => ['required','string','regex:/^[0-9]{10}$/'],
                'landmark'        => ['required','string'],
                'postal_code'     => ['required','string','regex:/^[0-9]{6}$/'],
                'state'           => ['required','string','exists:states,code'],
                'block_id'        => ['required','string','exists:blocks,name'],
            ];
        }

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        // Normalize only for authenticated flow
        if ($this->isAuthenticated && ($data['billing_is_shipping'] ?? false)) {
            $data['shipping_address'] = $data['billing_address'] ?? null;
        }

        return $data;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
