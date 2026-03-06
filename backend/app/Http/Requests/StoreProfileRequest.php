<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo'    => ['bail', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'headline' => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'summary'  => ['bail', 'required', 'string', 'min:10', 'max:2000'],
            'location' => ['bail', 'required', 'string', 'min:2', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'headline.required' => 'A headline is required (e.g. "Full Stack Developer").',
            'headline.min'      => 'Headline must be at least 2 characters.',
            'summary.required'  => 'Please write a brief summary about yourself.',
            'summary.min'       => 'Summary must be at least 10 characters.',
            'location.required' => 'Please provide your location.',
            'location.min'      => 'Location must be at least 2 characters.',
            'photo.image'       => 'The file must be an image.',
            'photo.mimes'       => 'Only JPG, PNG and WebP images are allowed.',
            'photo.max'         => 'Photo must be smaller than 2MB.',
        ];
    }
}
