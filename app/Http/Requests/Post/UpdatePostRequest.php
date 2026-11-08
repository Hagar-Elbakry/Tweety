<?php

namespace App\Http\Requests\Post;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('post'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['sometimes', 'nullable', 'string', 'max:255'],
            'image' => ['sometimes', 'nullable', 'image', File::types(['jpg', 'jpeg', 'png', 'gif'])->max(5 * 1024)],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $post = $this->route('post');

            $hasBody = $this->has('body') ? filled($this->input('body')) : filled($post->body);
            $hasImage = $this->hasFile('image') || (filled($post->image) && !$this->boolean('remove_image'));

            if (!$hasBody && !$hasImage) {
                $validator->errors()->add('body', 'A post needs text or an image.');
            }
        });
    }
}
