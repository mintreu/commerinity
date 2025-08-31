<?php

namespace App\Http\Requests\Lifecycle;

use Illuminate\Foundation\Http\FormRequest;

class UserOnboardingRequest extends FormRequest
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
            'name'              => 'required|string|max:255',
            'email'             => 'required|email',
            'mobile'            => 'required|string|size:10',
            'gender'            => 'nullable|string|in:male,female,other',
            'dob'               => 'nullable|date',
            'postal_code'       => 'required|string|size:6',
            'address_1'         => 'required|string|max:500',
            'landmark'          => 'nullable|string|max:255',
            'village'           => 'required|string|max:255',
            'city'              => 'required|string|max:255',
            'state_code'        => 'required|string|max:10',
            'block_id'          => 'required|string|max:255',
            'aadhaar'           => 'required|string|size:12',
            'pan'               => 'required|string|size:10',
            'tnc'               => 'accepted',
            'subscription_type' => 'required|in:subscribe,trial',
            'aadhaar_file'      => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'pan_file'          => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
        ];
    }
}
