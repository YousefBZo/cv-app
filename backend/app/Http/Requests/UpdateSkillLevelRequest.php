<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSkillLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'level' => ['bail', 'required', 'string', 'in:beginner,intermediate,advanced,expert'],
        ];
    }

    public function messages(): array
    {
        return [
            'level.required' => 'Skill level is required.',
            'level.in'       => 'Skill level must be beginner, intermediate, advanced, or expert.',
        ];
    }
}

