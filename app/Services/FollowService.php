<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Events\NewFollowCreated;
use App\Models\Activity;
use App\Models\User;

class FollowService
{
    public function toggleFollow(array $data, User $user): array
    {
        $userToFollow = User::query()->findOrFail($data['user_id']);
        $changes = $user->following()->toggle($userToFollow->id);
        if (! empty($changes['attached'])) {
            event(new NewFollowCreated($user, $userToFollow));

            return ['message' => 'Successfully followed the user.'];
        }
        Activity::query()->where('user_id', $user->id)
            ->where('type', ActivityType::FOLLOW)
            ->where('target_id', $userToFollow->id)
            ->delete();

        return ['message' => 'Successfully unfollowed the user.'];
    }
}
