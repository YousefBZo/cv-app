<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'organization'    => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'photo'           => ['bail', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'issue_date'      => ['bail', 'required', 'date', 'after_or_equal:1950-01-01', 'before_or_equal:today'],
            'expiration_date' => ['bail', 'nullable', 'date', 'after_or_equal:issue_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'              => 'Certificate name is required.',
            'name.min'                   => 'Certificate name must be at least 2 characters.',
            'organization.required'      => 'Issuing organization is required.',
            'organization.min'           => 'Organization name must be at least 2 characters.',
            'issue_date.required'        => 'Issue date is required.',
            'issue_date.after_or_equal'  => 'Issue date must be after January 1, 1950.',
            'issue_date.before_or_equal' => 'Issue date cannot be in the future.',
            'expiration_date.after_or_equal' => 'Expiration must be after issue date.',
            'photo.image'                => 'The file must be an image.',
            'photo.mimes'                => 'Only JPG, PNG and WebP images are allowed.',
        ];
    }
}

