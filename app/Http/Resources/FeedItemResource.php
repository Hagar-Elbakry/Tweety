<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserSimpleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $item = $this['data'];

        if ($this['type'] === 'repost') {
            return [
                'key' => "repost-{$item->id}",
                'type' => 'repost',
                'repost_type' => $item->type,
                'activity_at' => $this['sort_date']->toIsoString(),
                'reposted_by' => new UserSimpleResource($item->user),
                'comment' => $item->comment,
                'post' => new PostResource($item->post),
            ];
        }

        return [
            'key' => "post-{$item->id}",
            'type' => 'post',
            'activity_at' => $this['sort_date']->toIsoString(),
            'post' => new PostResource($item),
        ];
    }
}
