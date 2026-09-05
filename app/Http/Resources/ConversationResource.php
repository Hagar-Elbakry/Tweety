<?php

namespace App\Http\Resources;

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
        $otherUser = $this->users->firstWhere('id', '!=', $request->user()->id);
        return [
            'id' => $this->id,
            'other_user' => $otherUser ? [
                'name' => $otherUser->name,
                'avatar' => $otherUser->avatar,
            ] : null,
            'last_message' => $this->lastMessage ? [
                'body' => $this->lastMessage->body,
                'sent_at' => $this->lastMessage->created_at->toIsoString(),
            ] : null,
            'is_read' => $this->isReadFor($request->user()),
        ];
    }
}
