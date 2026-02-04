<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Casts\BeneficiaryTypeCast;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AdvisorTeamLeaderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'unique:users,mobile'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'string'],
            'kyc_type' => ['required', 'string'],
            'pan_number' => ['required', 'string', 'unique:kycs,pan_number'],
            'aadhaar_number' => ['nullable', 'string'],
            'company_name' => ['nullable', 'string'],
            'company_type' => ['nullable', 'string'],
            'gst_number' => ['nullable', 'string'],
            'address' => ['required', 'array'],
            'address.person_name' => ['required', 'string'],
            'address.person_mobile' => ['required', 'string'],
            'address.address_1' => ['required', 'string'],
            'address.city' => ['required', 'string'],
            'address.postal_code' => ['required', 'string'],
            'address.country_code' => ['required', 'string', 'size:2'],
            'address.state_code' => ['nullable', 'string'],
            'beneficiary' => ['nullable', 'array'],
            'beneficiary.type' => ['required_with:beneficiary', Rule::in(array_map(fn (BeneficiaryTypeCast $type) => $type->value, BeneficiaryTypeCast::cases()))],
            'beneficiary.account_number' => ['required_with:beneficiary', 'string'],
            'beneficiary.ifsc_code' => ['nullable', 'string'],
            'beneficiary.bank_name' => ['nullable', 'string'],
            'beneficiary.holder_name' => ['required_with:beneficiary', 'string'],
            'beneficiary.upi_id' => ['nullable', 'string'],
            'avatar' => ['nullable', 'file', 'image', 'max:2048'],
        ];
    }

    public function address(): array
    {
        return $this->validated('address');
    }

    public function beneficiary(): ?array
    {
        return $this->validated('beneficiary');
    }
}
