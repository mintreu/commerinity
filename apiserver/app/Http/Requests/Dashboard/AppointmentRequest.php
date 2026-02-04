<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Nullable;

final class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'agenda' => ['nullable', 'string'],
            'meeting_mode' => ['required', Rule::in(['online', 'offline'])],
            'meeting_link' => [
                'nullable',
                'url',
                Rule::requiredIf(fn () => $this->input('meeting_mode') === 'online'),
            ],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'attendee_type' => ['required', Rule::in(['user', 'admin'])],
            'attendee_uuid' => ['nullable', 'string'],
            'attendee_contact' => ['nullable', 'string'],
            'participants' => ['nullable', 'array'],
            'participants.*' => ['string', 'exists:users,uuid'],
            'advisor_uuid' => ['nullable', 'exists:users,uuid'],
            'mentor_uuid' => ['nullable', 'exists:users,uuid'],
        ];
    }

    public function attendeeType(): string
    {
        return $this->validated('attendee_type');
    }

    public function attendeeUuid(): string
    {
        return $this->validated('attendee_uuid', '');
    }

    public function advisorUuid(): ?string
    {
        return $this->validated('advisor_uuid', null);
    }

    public function mentorUuid(): ?string
    {
        return $this->validated('mentor_uuid', null);
    }

    public function attendeeContact(): ?string
    {
        return $this->validated('attendee_contact', null);
    }

    public function participants(): array
    {
        return $this->validated('participants', []);
    }
}
