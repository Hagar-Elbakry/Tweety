<?php

namespace App\Http\Resources;

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
            'sender_name' => $this->sender->name,
            'sender_avatar' => $this->sender->avatar,
            'body' => $this->body,
            'attachments' => $this->attachments->map(function ($attachment) {
                return [
                    'url' => Storage::url($attachment->path),
                    'type' => $attachment->type,
                    'original_name' => $attachment->original_name,
                ];
            }),
            'sent_at' => $this->created_at->toIsoString(),
            'read_by' => $this->seenBy->map(function ($read) {
                return [
                    'name' => $read->user->name,
                    'avatar' => $read->user->avatar,
                    'seen_at' => $read->seen_at->toIsoString(),
                ];
            }),
            'is_mine' => $request->user()->is($this->sender),
        ];
    }
}
