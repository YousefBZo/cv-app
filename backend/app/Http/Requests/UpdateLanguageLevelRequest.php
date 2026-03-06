<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLanguageLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'level' => ['bail', 'required', 'string', 'in:beginner,elementary,intermediate,upper_intermediate,advanced,native'],
        ];
    }

    public function messages(): array
    {
        return [
            'level.required' => 'Language level is required.',
            'level.in'       => 'Language level must be beginner, elementary, intermediate, upper_intermediate, advanced, or native.',
        ];
    }
}

