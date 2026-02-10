<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'person_name' => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
            'person_email' => ['nullable', 'email', 'max:255'],
            'person_mobile' => ['sometimes', 'required', 'string', 'digits:10'],
            'alternate_contact' => ['nullable', 'string', 'digits:10'],
            'type' => ['sometimes', 'required', 'string', Rule::in(['home', 'office', 'warehouse', 'store', 'pickup'])],
            'address_1' => ['sometimes', 'required', 'string', 'max:500'],
            'address_2' => ['nullable', 'string', 'max:500'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'required', 'string', 'max:255'],
            'postal_code' => ['sometimes', 'required', 'string', 'max:20'],
            'block_id' => ['nullable', 'integer', 'exists:blocks,id'],
            'state_code' => ['sometimes', 'required', 'string', 'exists:states,code'],
            'country_code' => ['sometimes', 'required', 'string', 'size:2', 'exists:countries,iso_code_2'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'default' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'pickup_location' => ['nullable', 'boolean'],
        ];
    }
}
