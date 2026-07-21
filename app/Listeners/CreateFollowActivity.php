<?php

namespace App\Listeners;

use App\Enums\ActivityType;
use App\Events\NewFollowCreated;
use App\Models\Activity;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateFollowActivity implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(NewFollowCreated $event): void
    {
        Activity::query()->create([
            'user_id' => $event->follower->id,
            'type' => ActivityType::FOLLOW,
            'target_id' => $event->following->id,
        ]);
    }
}
