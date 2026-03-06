<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['bail', 'required', 'string', 'current_password'],
            'password'         => ['bail', 'required', 'string', Password::min(8)->letters()->numbers(), 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'         => 'Current password is required.',
            'current_password.current_password'  => 'The current password is incorrect.',
            'password.required'                  => 'New password is required.',
            'password.min'                       => 'New password must be at least 8 characters.',
            'password.confirmed'                 => 'Password confirmation does not match.',
        ];
    }
}

