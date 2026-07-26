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
            $unreadNotifications = $request->user()->unreadNotifications()->latest()->get();
            $notifications = [];
            foreach ($unreadNotifications as $notification) {
                $notificationData = [
                    'type' => $notification->type,
                    'user' => [
                        'id' => $notification->data['user_id'],
                        'name' => $notification->data['user_name'],
                        'username' => $notification->data['user_username'],
                        'avatar' => $notification->data['user_avatar'] ? Storage::url($notification->data['user_avatar']) : null
                    ],
                    'message' => $notification->data['message'],
                    'created_at' => $notification->created_at
                ];
                if ($notification->type === 'Like') {
                    $notificationData['post'] = $notification->data['post'];
                } elseif ($notification->type === 'Comment') {
                    $notificationData['replying_to_username'] = $notification->data['replying_to_username'];
                    $notificationData['comment'] = $notification->data['comment'];
                }
                $notifications[] = $notificationData;
            }
            $request->user()->unreadNotifications()->update(['read_at' => now()]);

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
