<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'description' => ['bail', 'required', 'string', 'min:10', 'max:2000'],
            'link'        => ['bail', 'nullable', 'url', 'max:500'],
            'github_url'  => ['bail', 'nullable', 'url', 'max:500'],
            'start_date'  => ['bail', 'nullable', 'date', 'after_or_equal:1950-01-01', 'before_or_equal:today'],
            'end_date'    => ['bail', 'nullable', 'date', 'after_or_equal:start_date', 'before_or_equal:2036-12-31'],
            'cover'       => ['bail', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Project title is required.',
            'title.min'            => 'Project title must be at least 2 characters.',
            'description.required' => 'Please describe your project.',
            'description.min'      => 'Description must be at least 10 characters.',
            'link.url'             => 'Live link must be a valid URL.',
            'github_url.url'       => 'GitHub URL must be a valid URL.',
            'cover.image'          => 'Cover must be an image file.',
            'cover.mimes'          => 'Only JPG, PNG and WebP images are allowed.',
            'cover.max'            => 'Cover image must be smaller than 2MB.',
        ];
    }
}
