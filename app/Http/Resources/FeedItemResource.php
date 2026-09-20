<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this['type'] === 'repost') {
            return [
                'type' => 'repost',
                'repost_type' => $this['data']->type,
                'sort_date' => $this['sort_date'],
                'reposted_by' => $this['data']->user->name,
                'comment' => $this['data']->comment,
                'post' => new PostResource($this['data']->post),
            ];
        }

        return [
            'type' => 'post',
            'sort_date' => $this['sort_date'],
            'post' => new PostResource($this['data']),
        ];
    }
}
