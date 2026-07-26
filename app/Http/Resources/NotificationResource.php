<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this['type'],
            'user' => $this['user'],
            'post' => $this->when(isset($this['post']), fn() => $this['post']),
            'replying_to_username' => $this->when(isset($this['replying_to_username']),
                fn() => $this['replying_to_username']),
            'comment' => $this->when(isset($this['comment']), fn() => $this['comment']),
            'message' => $this['message'],
            'created_at' => $this['created_at']->format('Y-m-d H:i:s'),
        ];
    }
}
