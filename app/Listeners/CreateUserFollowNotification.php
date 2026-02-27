<?php

namespace App\Listeners;

use App\Events\NewFollowCreated;
use App\Models\User;
use App\Notifications\NewFollow;

class CreateUserFollowNotification
{
    /**
     * Handle the event.
     */
    public function handle(NewFollowCreated $event): void
    {
        $user = User::query()->findOrFail($event->following->id);
        $user->notify(new NewFollow($event->follower));
    }
}
