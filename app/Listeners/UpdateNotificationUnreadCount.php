<?php

namespace App\Listeners;

use App\Events\NotificationCountUpdated;
use Illuminate\Notifications\Events\NotificationSent;

class UpdateNotificationUnreadCount
{
    /**
     * Handle the event.
     */
    public function handle(NotificationSent $event): void
    {
        if ($event->channel !== 'database') {
            return;
        }
        $unreadCount = $event->notifiable->unreadNotifications()->count();
        broadcast(new NotificationCountUpdated($event->notifiable, $unreadCount));
    }
}
