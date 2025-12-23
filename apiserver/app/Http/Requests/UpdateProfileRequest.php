<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProfileRequest extends FormRequest
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
        $user = $this->user();

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'mobile' => [
                'nullable',
                'string',
                'regex:/^\+[1-9]\d{1,14}$/',
                Rule::unique('users')->ignore($user->id),
            ],
            'bio' => ['nullable', 'string', 'max:500'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'dob' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Your name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered to another account.',
            'mobile.regex' => 'Mobile number must be in international format (e.g., +919876543210).',
            'mobile.unique' => 'This mobile number is already registered to another account.',
            'bio.max' => 'Bio cannot exceed 500 characters.',
            'gender.in' => 'Please select a valid gender option (male, female, or other).',
            'dob.date' => 'Please provide a valid date of birth.',
            'dob.before' => 'Date of birth must be in the past.',
            'dob.after' => 'Please provide a valid date of birth.',
        ];
    }
}
