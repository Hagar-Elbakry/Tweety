<?php

namespace App\Services;

use App\Exceptions\NotFollowingException;
use App\Models\Follow;
use App\Models\User;

class FollowService
{
    public function follow(array $data): void
    {
        $userToFollow = User::query()->findOrFail($data['user_id']);
        if (! auth()->user()->isFollowing($userToFollow)) {
            Follow::query()->create([
                'follower_id' => auth()->id(),
                'following_id' => $userToFollow->id,
            ]);
        }
    }

    public function unfollow(array $data): void
    {
        $userToUnfollow = User::query()->findOrFail($data['user_id']);
        $follow = Follow::query()->where('follower_id', auth()->id())
            ->where('following_id', $userToUnfollow->id)
            ->first();
        if (! $follow) {
            throw new NotFollowingException;
        }
        $follow->delete();
    }
}
