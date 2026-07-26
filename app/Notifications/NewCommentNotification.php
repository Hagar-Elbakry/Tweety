<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected User $user,
        protected User $replyingToUser,
        protected Comment $comment
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
        return 'Comment';
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
            'replying_to_username' => $this->replyingToUser->username,
            'comment' => $this->comment->body,
            'message' => 'commented on your post',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_username' => $this->user->username,
            'user_avatar' => $this->user->avatar,
            'replying_to_username' => $this->replyingToUser->username,
            'comment' => $this->comment->body,
            'message' => 'commented on your post',
        ]);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('comment-notifications.'.$this->comment->post->user_id);
    }
}
