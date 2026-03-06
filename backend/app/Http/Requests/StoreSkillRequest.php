<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'skills'         => ['bail', 'required', 'array', 'min:1', 'max:50'],
            'skills.*.name'  => ['bail', 'required', 'string', 'min:1', 'max:255'],
            'skills.*.level' => ['bail', 'required', 'string', 'in:beginner,intermediate,advanced,expert'],
        ];
    }

    public function messages(): array
    {
        return [
            'skills.required'       => 'Please select at least one skill.',
            'skills.min'            => 'Please select at least one skill.',
            'skills.max'            => 'You can add up to 50 skills.',
            'skills.*.name.required'=> 'Skill name is required.',
            'skills.*.level.in'     => 'Skill level must be beginner, intermediate, advanced, or expert.',
        ];
    }
}
