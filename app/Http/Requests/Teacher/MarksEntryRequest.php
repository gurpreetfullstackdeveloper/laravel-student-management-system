<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class MarksEntryRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'teacher'; }
    public function rules(): array
    {
        return [
            'marks' => ['required', 'array'],
            'marks.*' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', 'string'],
        ];
    }
}