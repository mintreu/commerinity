<?php

namespace App\Http\Requests\Helpdesk;

use Illuminate\Foundation\Http\FormRequest;

class StoreHelpdeskRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority'    => ['required', 'in:low,medium,high,urgent'],
            'topic_slug'    => ['required', 'exists:helpdesk_topics,slug'],
            'screenshot'  => ['nullable', 'image', 'max:5120'], // 5MB
        ];
    }

}
