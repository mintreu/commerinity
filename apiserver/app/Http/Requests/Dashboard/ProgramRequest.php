<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ProgramRequest extends FormRequest
{
    public const STATUS_CHOICES = [
        'draft',
        'scheduled',
        'ongoing',
        'completed',
        'cancelled',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(self::STATUS_CHOICES)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'address' => ['nullable', 'array'],
            'address.person_name' => ['required_with:address', 'string'],
            'address.person_mobile' => ['required_with:address', 'string'],
            'address.address_1' => ['required_with:address', 'string'],
            'address.city' => ['required_with:address', 'string'],
            'address.postal_code' => ['required_with:address', 'string'],
            'address.country_code' => ['required_with:address', 'string', 'size:2'],
            'address.state_code' => ['nullable', 'string'],
            'participants' => ['nullable', 'array'],
            'participants.*.uuid' => ['required_with:participants', 'exists:users,uuid'],
            'participants.*.role' => ['nullable', 'string'],
        ];
    }

    public function participants(): array
    {
        return $this->validated('participants', []);
    }

    public function address(): ?array
    {
        return $this->validated('address', null);
    }

    public function status(): string
    {
        return $this->validated('status', 'draft');
    }
}
