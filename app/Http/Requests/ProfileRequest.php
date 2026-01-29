<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', Rule::unique('users', 'username')->ignore($this->user)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user)],
            'password' => ['sometimes', 'nullable', 'min:8', 'confirmed'],
            'avatar' => ['sometimes', 'nullable', 'image', 'max:2048', 'mimes:jpeg,jpg,png'],
            'banner' => ['sometimes', 'nullable', 'image', 'max:4096', 'mimes:jpeg,jpg,png'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
