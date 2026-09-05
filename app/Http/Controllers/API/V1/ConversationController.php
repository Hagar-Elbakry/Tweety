<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Models\User;
use App\Services\ConversationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    public function __construct(
        protected ConversationService $conversationService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $conversations = $this->conversationService->getUserConversations($request->user());
            return ApiResponse::success(message: 'Conversations retrieved successfully',
                data: ConversationResource::collection($conversations));
        } catch (Exception $e) {
            Log::error('Failed to retrieve messages: '.$e->getMessage());
            return ApiResponse::error(message: 'Failed to retrieve messages.');
        }
    }

    public function store(StoreConversationRequest $request): JsonResponse
    {
        try {
            $recipient = User::find($request->recipient_id);
            $conversation = $this->conversationService->findOrCreateBetween($request->user(), $recipient);
            return ApiResponse::success(message: 'Conversation created successfully',
                data: ['conversation_id' => $conversation->id]);
        } catch (Exception $e) {
            Log::error('Failed to create conversation: '.$e->getMessage());
            return ApiResponse::error(message: 'Failed to create conversation');
        }
    }
}
