<?php

namespace App\Http\Requests\Recruitment;

use Illuminate\Foundation\Http\FormRequest;

class ApplyRecruitmentRequest extends FormRequest
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
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email'],
            'mobile'            => ['required', 'string', 'max:15'],
            'gender'            => ['required', 'in:male,female,other'],
            'dob'               => ['required', 'date'],
            'guardian_name'     => ['nullable', 'string', 'max:255'],
            'address_uuid'      => ['required', 'exists:addresses,uuid'],
            'company_name'      => ['nullable', 'string', 'max:255'],
            'educations'        => ['required', 'array', 'min:1'],
            'educations.*.degree'      => ['required', 'string'],
            'educations.*.institution' => ['required', 'string'],
            'educations.*.year'        => ['required', 'integer'],
            'experiences'       => ['nullable', 'array'],
            'experiences.*.company_name' => ['required_with:experiences', 'string'],
            'experiences.*.start'       => ['required_with:experiences', 'date'],
            'experiences.*.end'         => ['required_with:experiences', 'date', 'after_or_equal:experiences.*.start'],
            'experiences.*.experience'  => ['required_with:experiences', 'string'],
            'skills'            => ['required', 'array', 'min:1'],
            'skills.*.skill'    => ['required', 'string'],
            'skills.*.description' => ['required', 'string'],
            'reference_name'    => ['nullable', 'string'],
            'reference_contact' => ['nullable', 'string'],
            'relocation'        => ['required', 'boolean'],
            'agree_terms'       => ['accepted'],
        ];
    }
}
