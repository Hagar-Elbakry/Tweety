<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewRepostNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected User $user,
        protected Post $post,
        protected string $type
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
        return 'Repost';
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_username' => $this->user->username,
            'user_avatar' => $this->user->avatar,
            'post' => $this->post->body,
            'message' => $this->type === 'repost' ? 'reposted your post' : 'quoted your post'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_username' => $this->user->username,
            'user_avatar' => $this->user->avatar,
            'post' => $this->post->body,
            'message' => $this->type === 'repost' ? 'reposted your post' : 'quoted your post'
        ]);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('repost.'.$this->post->user_id);
    }
}
