<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NotificationsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $unreadNotifications = $request->user()->unreadNotifications;
            $notifications = [];
            foreach ($unreadNotifications as $notification) {
                if ($notification->type === 'Follow') {
                    $notifications[] = [
                        'type' => 'follow',
                        'user' => [
                            'name' => $notification->data['follower_name'],
                            'username' => $notification->data['follower_username'],
                            'avatar' => $notification->data['follower_avatar'] ? Storage::url($notification->data['follower_avatar']) : null,
                        ],
                        'created_at' => $notification->created_at,
                    ];
                }
            }
            $request->user()->unreadNotifications->markAsRead();

            return ApiResponse::success(message: 'Notifications fetched successfully',
                data: NotificationResource::collection($notifications));
        } catch (Exception $e) {
            Log::error('Failed to fetch notifications: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(message: 'Failed to fetch notifications', status: 500);
        }
    }
}
