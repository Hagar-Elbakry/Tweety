<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class NotificationsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $unreadNotifications = auth()->user()->unreadNotifications;
        $followerIds = $unreadNotifications->where('type',
            'App\Notifications\NewFollow')->pluck('data.follower_id')->toArray();
        $followers = User::query()->whereIn('id', $followerIds)->get()->keyBy('id');
        $notifications = [];
        foreach ($unreadNotifications as $notification) {
            $notification->markAsRead();
            if ($notification->type === 'App\Notifications\NewFollow') {
                $notifications[] = [
                    'type' => 'follow',
                    'user' => $followers[$notification->data['follower_id']],
                    'created_at' => $notification->created_at,
                ];
            }
        }

        return ApiResponse::success(message: 'Notifications fetched successfully',
            data: NotificationResource::collection($notifications));
    }
}
