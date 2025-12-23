<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class OnboardingProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth via middleware
    }

    public function rules(): array
    {
        $user = $this->user();

        return [
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'bio' => ['nullable', 'string', 'max:500'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'dob' => ['required', 'date', 'before:today', 'after:1900-01-01'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your full name',
            'name.min' => 'Name must be at least 3 characters',
            'gender.required' => 'Please select your gender',
            'gender.in' => 'Please select a valid gender option',
            'dob.required' => 'Date of birth is required',
            'dob.before' => 'Date of birth must be in the past',
            'dob.after' => 'Please enter a valid date of birth',
            'email.unique' => 'This email is already registered',
        ];
    }
}
