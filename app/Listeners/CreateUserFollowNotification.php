<?php

namespace App\Listeners;

use App\Events\NewFollowCreated;
use App\Models\User;

class CreateUserFollowNotification
{
    /**
     * Handle the event.
     */
    public function handle(NewFollowCreated $event): void
    {
        $user = User::query()->findOrFail($event->follow->following_id);
        $user->notify(new NewFollowCreated($event->follow));
    }
}
