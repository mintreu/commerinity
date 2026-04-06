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
        $maxDob = now()->subYears(18)->toDateString();
        $minDob = now()->subYears(100)->toDateString();

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
            'dob' => ['required', 'date', "before_or_equal:{$maxDob}", "after_or_equal:{$minDob}"],
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
            'dob.before_or_equal' => 'You must be at least 18 years old',
            'dob.after_or_equal' => 'Date of birth must be within the last 100 years',
            'email.unique' => 'This email is already registered',
        ];
    }
}
