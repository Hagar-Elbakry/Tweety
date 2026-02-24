<?php

namespace App\Listeners;

use App\Events\NewFollowCreated;
use App\Models\Activity;

class CreateFollowActivity
{
    /**
     * Handle the event.
     */
    public function handle(NewFollowCreated $event): void
    {
        Activity::query()->create([
            'user_id' => $event->follow->follower_id,
            'type' => 'follow',
            'target_id' => $event->follow->following_id,
        ]);
    }
}
