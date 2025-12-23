<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:10', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'topic_slug' => ['required', 'exists:helpdesk_topics,slug'],
            'screenshot' => ['nullable', 'file', 'image', 'max:10240'],
        ];
    }

    public function prepareForValidation(): void
    {
        if ($this->has('topic_slug')) {
            $topic = \App\Models\HelpdeskTopic::where('slug', $this->topic_slug)->first();
            if ($topic) {
                $this->merge(['topic_id' => $topic->id]);
            }
        }
    }
}
