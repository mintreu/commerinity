<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SubmitKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kycType = $this->input('kyc_type', \App\Casts\KycTypeCast::PERSONAL->value);

        $rules = [
            'kyc_type' => ['required', 'string', Rule::in([\App\Casts\KycTypeCast::PERSONAL->value, \App\Casts\KycTypeCast::BUSINESS->value])],
            'pan_number' => [
                'required',
                'string',
                'size:10',
                'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
                Rule::unique('kycs', 'pan_number')->whereNull('deleted_at'),
            ],
            'aadhaar_number' => [
                'nullable',
                'string',
                'size:12',
                'regex:/^[0-9]{12}$/',
            ],
            'documents' => ['nullable', 'array'],
            'documents.*' => [
                'file',
                'mimes:jpeg,png,pdf',
                'max:5120',
            ],
            'pan_image' => ['required_without:id', 'file', 'mimes:jpeg,png', 'max:5120'],
            'aadhaar_image' => ['required_without:id', 'file', 'mimes:jpeg,png', 'max:5120'],
            'gst_image' => ['nullable', 'file', 'mimes:jpeg,png', 'max:5120'],
        ];

        if ($kycType === 'business') {
            $rules['company_name'] = ['required', 'string', 'min:2', 'max:255'];
            $rules['company_type'] = [
                'required',
                'string',
                Rule::in(['sole_proprietor', 'partnership', 'llp', 'private_limited', 'public_limited', 'huf']),
            ];
            $rules['gst_number'] = [
                'required',
                'string',
                'size:15',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                Rule::unique('kycs', 'gst_number')->whereNull('deleted_at'),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'kyc_type.required' => 'KYC type is required.',
            'kyc_type.in' => 'KYC type must be either personal or business.',
            'pan_number.required' => 'PAN number is required.',
            'pan_number.regex' => 'Invalid PAN format. Must be like ABCDE1234F.',
            'pan_number.unique' => 'This PAN is already registered.',
            'aadhaar_number.regex' => 'Aadhaar must be exactly 12 digits.',
            'company_name.required' => 'Company name is required for business KYC.',
            'company_type.required' => 'Company type is required for business KYC.',
            'gst_number.required' => 'GST number is required for business KYC.',
            'gst_number.regex' => 'Invalid GST format.',
            'gst_number.unique' => 'This GST number is already registered.',
            'documents.*.required' => 'At least one document is required.',
            'documents.*.mimes' => 'Documents must be JPEG, PNG, or PDF.',
            'documents.*.max' => 'Document size must not exceed 5MB.',
        ];
    }
}
