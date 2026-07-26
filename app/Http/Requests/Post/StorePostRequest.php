<?php

namespace App\Http\Requests\Post;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StorePostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required_without:image', 'nullable', 'max:255'],
            'image' => [
                'required_without:body', 'nullable', 'image', File::types(['jpg', 'jpeg', 'png', 'gif'])->max(5 * 1024),
            ],
        ];
    }
}
