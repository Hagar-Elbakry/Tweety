<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetConversationMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Services\MessageService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function __construct(
        protected MessageService $messageService,
    ) {
    }

    public function index(GetConversationMessageRequest $request, Conversation $conversation): JsonResponse
    {
        try {
            $messages = $this->messageService->getMessagesForConversation($conversation);
            return ApiResponse::success(message: 'Messages retrieved successfully',
                data: MessageResource::collection($messages));
        } catch (Exception $e) {
            Log::error('Failed to retrieve messages: '.$e->getMessage());
            return ApiResponse::error(message: 'Failed to retrieve messages');
        }
    }
}
