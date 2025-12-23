<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'person_name' => ['required', 'string', 'min:2', 'max:255'],
            'person_email' => ['nullable', 'email', 'max:255'],
            'person_mobile' => ['required', 'string', 'regex:/^\+[1-9]\d{1,14}$/'],
            'alternate_contact' => ['nullable', 'string', 'regex:/^\+[1-9]\d{1,14}$/'],
            'type' => ['required', 'string', Rule::in(['home', 'office', 'warehouse', 'store', 'pickup'])],
            'address_1' => ['required', 'string', 'max:500'],
            'address_2' => ['nullable', 'string', 'max:500'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'block_id' => ['nullable', 'integer', 'exists:blocks,id'],
            'state_code' => ['required', 'string', 'exists:states,code'],
            'country_code' => ['required', 'string', 'size:2', 'exists:countries,iso_code_2'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'default' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'pickup_location' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'person_name.required' => 'Contact person name is required.',
            'person_mobile.required' => 'Contact mobile number is required.',
            'person_mobile.regex' => 'Mobile number must be in E.164 format.',
            'type.required' => 'Address type is required.',
            'address_1.required' => 'Address line 1 is required.',
            'city.required' => 'City is required.',
            'postal_code.required' => 'Postal code is required.',
            'state_code.required' => 'State is required.',
            'country_code.required' => 'Country is required.',
        ];
    }
}
