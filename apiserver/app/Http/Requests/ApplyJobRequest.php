<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for job application submission.
 *
 * Handles validation for applying to a recruitment position.
 * Most applicant data comes from the user's profile.
 */
class ApplyJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'guardian_name' => ['required', 'string', 'min:3', 'max:100'],
            'address_id' => ['nullable', 'integer', 'exists:addresses,id'],

            // Educations (optional, array of education records)
            'educations' => ['nullable', 'array', 'max:5'],
            'educations.*.degree' => ['required_with:educations', 'string', 'max:100'],
            'educations.*.institution' => ['required_with:educations', 'string', 'max:200'],
            'educations.*.year' => ['required_with:educations', 'integer', 'min:1950', 'max:'.date('Y')],

            // Skills (optional, array of skill records)
            'skills' => ['nullable', 'array', 'max:10'],
            'skills.*.skill' => ['required_with:skills', 'string', 'max:100'],
            'skills.*.description' => ['nullable', 'string', 'max:500'],

            // Experiences (optional, array of experience records)
            'experiences' => ['nullable', 'array', 'max:5'],
            'experiences.*.company' => ['required_with:experiences', 'string', 'max:200'],
            'experiences.*.role' => ['required_with:experiences', 'string', 'max:100'],
            'experiences.*.duration' => ['nullable', 'string', 'max:50'],
            'experiences.*.description' => ['nullable', 'string', 'max:500'],

            // Reference (optional)
            'reference_name' => ['nullable', 'string', 'max:100'],
            'reference_contact' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'guardian_name.required' => 'Please provide your guardian or parent name.',
            'guardian_name.min' => 'Guardian name must be at least 3 characters.',
            'address_id.exists' => 'Please select a valid address from your profile.',
            'educations.max' => 'You can add up to 5 education qualifications.',
            'skills.max' => 'You can add up to 10 skills.',
            'experiences.max' => 'You can add up to 5 work experiences.',
            'educations.*.year.min' => 'Year must be 1950 or later.',
            'educations.*.year.max' => 'Year cannot be in the future.',
        ];
    }
}
