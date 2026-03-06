<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'languages'         => ['bail', 'required', 'array', 'min:1', 'max:30'],
            'languages.*.name'  => ['bail', 'required', 'string', 'min:1', 'max:255'],
            'languages.*.level' => ['bail', 'required', 'string', 'in:beginner,elementary,intermediate,upper_intermediate,advanced,native'],
        ];
    }

    public function messages(): array
    {
        return [
            'languages.required'        => 'Please select at least one language.',
            'languages.min'             => 'Please select at least one language.',
            'languages.max'             => 'You can add up to 30 languages.',
            'languages.*.name.required' => 'Language name is required.',
            'languages.*.level.in'      => 'Invalid proficiency level.',
        ];
    }
}
