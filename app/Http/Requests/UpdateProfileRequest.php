<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 'regex:/^(?![!@#$%^&*])[A-Za-z0-9_]+$/',
                'bail',
                Rule::unique('users', 'username')->ignore($this->user()->id),
            ],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'password' => ['sometimes', 'nullable', 'min:8', 'confirmed'],
            'avatar' => [
                'sometimes', 'nullable', 'image', File::types(['jpeg', 'jpg', 'png'])->min(1024)->max(12 * 1024),
            ],
            'banner' => [
                'sometimes', 'nullable', 'image', File::types(['jpeg', 'jpg', 'png'])->min(1024)->max(12 * 1024),
            ],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'The username may only contain letters, numbers, and underscores, and cannot start with special characters.',
        ];
    }
}
