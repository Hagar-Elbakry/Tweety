<?php

namespace App\Services;

use App\Events\NewFollowCreated;
use App\Models\User;

class FollowService
{
    public function toggleFollow(array $data): array
    {
        $userToFollow = User::query()->findOrFail($data['user_id']);
        $changes = auth()->user()->following()->toggle($userToFollow->id);
        if (! empty($changes['attached'])) {
            event(new NewFollowCreated(auth()->user(), $userToFollow));

            return ['message' => 'Successfully followed the user. '];
        }

        return ['message' => 'Successfully unfollowed the user. '];
    }
}
