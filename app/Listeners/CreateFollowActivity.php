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
            'user_id' => $event->follower->id,
            'type' => 'follow',
            'target_id' => $event->following->id,
        ]);
    }
}
