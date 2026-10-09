<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostRepost;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedService
{
    private const PER_PAGE = 10;
    private const COUNTS = ['comments', 'likes', 'bookmarks', 'reposts'];

    public function getTimeline(User $user): LengthAwarePaginator
    {
        $userIds = $user->following()->pluck('users.id')->push($user->id);

        $posts = Post::whereIn('user_id', $userIds)
            ->withCount(self::COUNTS)
            ->with('user')
            ->get()
            ->map(fn($post) => [
                'type' => 'post',
                'sort_date' => $post->created_at,
                'data' => $post,
            ]);

        $reposts = PostRepost::whereIn('user_id', $userIds)
            ->with([
                'user',
                'post' => fn($query) => $query->withCount(self::COUNTS)->with('user'),
            ])
            ->get()
            ->map(fn($repost) => [
                'type' => 'repost',
                'sort_date' => $repost->created_at,
                'data' => $repost,
            ]);

        $timeline = $posts->merge($reposts)->sortByDesc('sort_date')->values();
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $timeline->forPage($page, self::PER_PAGE)->values(),
            $timeline->count(),
            self::PER_PAGE,
            $page,
            ['path' => request()->url()]
        );
    }
}
