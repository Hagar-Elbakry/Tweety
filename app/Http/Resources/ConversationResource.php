<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserSimpleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $otherUser = $this->users->firstWhere('id', '!=', $user->id);

        return [
            'id' => $this->id,
            'other_user' => $otherUser ? new UserSimpleResource($otherUser) : null,
            'last_message' => $this->lastMessage ? [
                'id' => $this->lastMessage->id,
                'body' => $this->lastMessage->body,
                'sender_id' => $this->lastMessage->sender_id,
                'created_at' => $this->lastMessage->created_at->toIsoString(),
            ] : null,
            'is_read' => $this->isReadFor($user),
        ];
    }
}
