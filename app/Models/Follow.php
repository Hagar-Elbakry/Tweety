<?php

namespace App\Models;

use App\Events\NewFollowCreated;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    protected $fillable = [
        'follower_id',
        'following_id',
    ];

    protected $dispatchesEvents = [
        'created' => NewFollowCreated::class,
    ];
}
