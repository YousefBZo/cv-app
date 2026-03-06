<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVolunteerExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization' => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'role'         => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'start_date'   => ['bail', 'required', 'date', 'after_or_equal:1950-01-01', 'before_or_equal:today'],
            'end_date'     => ['bail', 'nullable', 'date', 'after_or_equal:start_date', 'before_or_equal:2036-12-31'],
            'description'  => ['bail', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'organization.required'      => 'Organization name is required.',
            'organization.min'           => 'Organization name must be at least 2 characters.',
            'role.required'              => 'Your role is required.',
            'role.min'                   => 'Role must be at least 2 characters.',
            'start_date.required'        => 'Start date is required.',
            'start_date.after_or_equal'  => 'Start date must be after January 1, 1950.',
            'start_date.before_or_equal' => 'Start date cannot be in the future.',
            'end_date.after_or_equal'    => 'End date must be after the start date.',
        ];
    }
}

