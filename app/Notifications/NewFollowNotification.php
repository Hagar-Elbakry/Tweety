<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewFollowNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected User $follower,
        protected User $following
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function databaseType(): string
    {
        return 'Follow';
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->follower->id,
            'user_name' => $this->follower->name,
            'user_username' => $this->follower->username,
            'user_avatar' => $this->follower->avatar,
            'message' => 'started following you',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'user_id' => $this->follower->id,
            'user_name' => $this->follower->name,
            'user_username' => $this->follower->username,
            'user_avatar' => $this->follower->avatar,
            'message' => 'started following you',
        ]);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('follow-notifications.'.$this->following->id);
    }
}
