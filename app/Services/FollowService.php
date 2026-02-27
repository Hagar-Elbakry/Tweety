<?php

namespace App\Services;

use App\Models\User;

class FollowService
{
    public function follow(array $data): void
    {
        $userToFollow = User::query()->findOrFail($data['user_id']);
        auth()->user()->following()->syncWithoutDetaching($userToFollow->id);
    }

    public function unfollow(array $data): void
    {
        $userToUnfollow = User::query()->findOrFail($data['user_id']);
        auth()->user()->following()->detach($userToUnfollow->id);
    }
}
