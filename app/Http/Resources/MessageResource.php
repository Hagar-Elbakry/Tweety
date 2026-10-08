<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserSimpleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'body' => $this->body,
            'sender' => $this->whenLoaded('sender',
                fn() => $this->sender ? new UserSimpleResource($this->sender) : null),
            'attachments' => $this->whenLoaded('attachments', fn() => $this->attachments->map(fn($attachment) => [
                'id' => $attachment->id,
                'url' => Storage::url($attachment->path),
                'type' => $attachment->type,
                'original_name' => $attachment->original_name,
            ])),
            'read_by' => $this->whenLoaded('seenBy', fn() => $this->seenBy->map(fn($read) => [
                'user' => new UserSimpleResource($read->user),
                'seen_at' => $read->seen_at->toIsoString(),
            ])),
            'is_mine' => $this->when($request->user() !== null, fn() => $request->user()->id === $this->sender_id),
            'created_at' => $this->created_at->toIsoString(),
            'updated_at' => $this->updated_at->toIsoString(),
        ];
    }
}
