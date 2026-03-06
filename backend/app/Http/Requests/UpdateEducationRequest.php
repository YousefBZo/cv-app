<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEducationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'institution'    => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'degree'         => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'field_of_study' => ['bail', 'nullable', 'string', 'max:255'],
            'start_date'     => ['bail', 'required', 'date', 'after_or_equal:1950-01-01', 'before_or_equal:today'],
            'end_date'       => ['bail', 'nullable', 'date', 'after_or_equal:start_date', 'before_or_equal:2036-12-31'],
            'description'    => ['bail', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'institution.required'       => 'Institution name is required.',
            'institution.min'            => 'Institution name must be at least 2 characters.',
            'degree.required'            => 'Degree is required.',
            'degree.min'                 => 'Degree must be at least 2 characters.',
            'start_date.required'        => 'Start date is required.',
            'start_date.after_or_equal'  => 'Start date must be after January 1, 1950.',
            'start_date.before_or_equal' => 'Start date cannot be in the future.',
            'end_date.after_or_equal'    => 'End date must be after the start date.',
        ];
    }
}

