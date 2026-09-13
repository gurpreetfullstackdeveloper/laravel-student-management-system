<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'student'; }

    public function rules(): array
    {
        return [
            'guardian_phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:65535'],
        ];
    }
}