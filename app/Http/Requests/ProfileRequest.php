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
            'name' => 'required',
            'username' => ['required', Rule::unique('users', 'username')->ignore($this->user)],
            'email' => ['required','email', Rule::unique('users', 'email')->ignore($this->user)],
            'password' => 'required|min:8|confirmed',
            'avatar' => 'nullable|image|max:2048|mimes:jpeg,jpg,png',
            'banner' => 'nullable|image|max:4096|mimes:jpeg,jpg,png',
            'bio' => 'nullable|string|max:1000',
        ];
    }
}
