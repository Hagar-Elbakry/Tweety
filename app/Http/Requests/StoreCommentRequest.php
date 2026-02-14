<?php

namespace App\Http\Requests;

use App\Models\Comment;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:1000'],
            'parent_id' => [
                'nullable',
                'exists:comments,id',
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($value) {
                        $parentComment = Comment::find($value);
                        if ($parentComment && $parentComment->post_id != $this->route('post')->id) {
                            $fail('The parent comment must belong to the same post.');
                        }
                    }
                },
            ],
        ];
    }
}
