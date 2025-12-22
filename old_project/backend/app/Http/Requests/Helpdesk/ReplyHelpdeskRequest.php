<?php

namespace App\Http\Requests\Helpdesk;

use Illuminate\Foundation\Http\FormRequest;

class ReplyHelpdeskRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message'      => ['required', 'string'],
            'attachments.*'=> ['nullable', 'file', 'max:10240'], // 10MB per file
        ];
    }

}
