<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * OPTIMIZATION #9 — Bail Early on Validation
     *
     * WHY: Without 'bail', Laravel runs ALL validation rules for a field
     *      even if the first one fails. Example: if 'company' is null,
     *      it still checks 'string', 'min:2', 'max:255' — wasting CPU.
     *      'bail' stops at the first failure for each field.
     *
     * IMPACT: Faster error responses (fewer rules evaluated per invalid request).
     */
    public function rules(): array
    {
        return [
            'company'     => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'position'    => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'start_date'  => ['bail', 'required', 'date', 'after_or_equal:1950-01-01', 'before_or_equal:today'],
            'end_date'    => ['bail', 'nullable', 'date', 'after_or_equal:start_date', 'before_or_equal:2036-12-31'],
            'description' => ['bail', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'company.required'          => 'Company name is required.',
            'company.min'               => 'Company name must be at least 2 characters.',
            'position.required'         => 'Position/role is required.',
            'position.min'              => 'Position must be at least 2 characters.',
            'start_date.required'       => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date must be after January 1, 1950.',
            'start_date.before_or_equal'=> 'Start date cannot be in the future.',
            'end_date.after_or_equal'   => 'End date must be after the start date.',
            'end_date.before_or_equal'  => 'End date seems unrealistic.',
        ];
    }
}
