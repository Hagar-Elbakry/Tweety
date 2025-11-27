<?php

namespace App\Models;

use App\Policies\PostPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
#[UsePolicy(PostPolicy::class)]
class Post extends Model
{
    protected $fillable = ['body', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
