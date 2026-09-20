<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostRepost;
use App\Models\User;

class FeedService
{
    public function getTimeline(User $user)
    {
        $userIds = $user->following()->pluck('users.id')->push($user->id);
        $posts = Post::whereIn('user_id', $userIds)->withCount([
            'comments', 'likes', 'bookmarks', 'reposts',
        ])->with('user')->get();
        $reposts = PostRepost::whereIn('user_id', $userIds)
            ->with(['user', 'post.user'])
            ->with([
                'post' => function ($query) {
                    $query->withCount(['comments', 'likes', 'bookmarks', 'reposts']);
                },
            ])
            ->get();

        $formattedPosts = $posts->map(function ($post) {
            return [
                'type' => 'post',
                'sort_date' => $post->created_at,
                'data' => $post,
            ];
        });
        $formattedReposts = $reposts->map(function ($repost) {
            return [
                'type' => 'repost',
                'sort_date' => $repost->created_at,
                'data' => $repost,
            ];
        });

        $timeline = $formattedPosts->merge($formattedReposts)->sortByDesc('sort_date');

        $page = request()->get('page', 1);
        $perPage = 10;
        $items = $timeline->values()->forPage($page, $perPage)->values();

        return $items;
    }
}
