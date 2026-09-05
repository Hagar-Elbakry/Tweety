<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversationRequest;
use App\Models\User;
use App\Services\ConversationService;
use Exception;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    public function __construct(
        protected ConversationService $conversationService,
    ) {
    }

    public function store(StoreConversationRequest $request)
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
