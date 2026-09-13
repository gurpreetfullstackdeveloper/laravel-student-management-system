<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'teacher'; }
    public function rules(): array { return ['phone' => ['nullable', 'string', 'max:255'], 'qualification' => ['nullable', 'string', 'max:255']]; }
}