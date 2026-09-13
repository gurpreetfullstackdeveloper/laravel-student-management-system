<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceBulkRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'teacher'; }
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', 'in:present,absent,late,leave'],
        ];
    }
}