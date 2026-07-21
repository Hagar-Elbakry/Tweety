<?php

namespace App\Listeners;

use App\Events\NewFollowCreated;
use App\Notifications\NewFollowNotification;

class CreateUserFollowNotification
{
    /**
     * Handle the event.
     */
    public function handle(NewFollowCreated $event): void
    {
        $event->following->notify(new NewFollowNotification($event->follower, $event->following));
    }
}
