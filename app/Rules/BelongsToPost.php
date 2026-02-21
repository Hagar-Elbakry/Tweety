<?php

namespace App\Rules;

use App\Models\Comment;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class BelongsToPost implements ValidationRule
{
    public function __construct(
        protected $postId
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parentComment = Comment::query()
            ->where('id', $value)
            ->where('post_id', $this->postId)
            ->first();
        if (! $parentComment) {
            $fail('The selected parent comment is invalid or belongs to another post.');
        }
    }
}
