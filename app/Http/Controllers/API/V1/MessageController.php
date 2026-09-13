<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteMessageRequest;
use App\Http\Requests\GetConversationMessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
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
            $messages = $this->messageService->getMessagesForConversation($conversation, $request->user());
            return ApiResponse::success(message: 'Messages retrieved successfully',
                data: MessageResource::collection($messages));
        } catch (Exception $e) {
            Log::error('Failed to retrieve messages: '.$e->getMessage());
            return ApiResponse::error(message: 'Failed to retrieve messages');
        }
    }

    public function store(StoreMessageRequest $request, Conversation $conversation): JsonResponse
    {
        try {
            $message = $this->messageService->sendMessage($conversation, $request->validated('body'), $request->validated('attachments'), $request->user());
            return ApiResponse::success(message: 'Message sent', data: new MessageResource($message));
        } catch (Exception $e) {
            Log::error('Failed to send message: '.$e->getMessage());
            return ApiResponse::error(message: 'Failed to send message');
        }
    }

    public function update(UpdateMessageRequest $request, Conversation $conversation, Message $message): JsonResponse
    {
        try {
            $message = $this->messageService->updateMessage($message, $request->validated('body'));
            return ApiResponse::success(message: 'Message updated successfully', data: new MessageResource($message));
        } catch (Exception $e) {
            Log::error('Failed to update message: '.$e->getMessage());
            return ApiResponse::error(message: 'Failed to update message');
        }
    }

    public function destroy(DeleteMessageRequest $request, Conversation $conversation, Message $message): JsonResponse
    {
        try {
            $this->messageService->deleteForUser($message, $request->user());
            return ApiResponse::success(message: 'Message deleted successfully');
        } catch (Exception $e) {
            Log::error('Failed to delete message: '.$e->getMessage());
            return ApiResponse::error(message: 'Failed to delete message');
        }
    }
}
