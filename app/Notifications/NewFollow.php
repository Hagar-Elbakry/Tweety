<?php

namespace App\Notifications;

use App\Models\Follow;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFollow extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Follow $follow
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'follower_id' => $this->follow->follower_id,
        ];
    }
}
