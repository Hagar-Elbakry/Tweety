<?php

namespace App\Http\Controllers\API\V1;

use App\Events\UserTyping;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\TypingRequest;
use App\Models\Conversation;

class TypingController extends Controller
{
    public function __invoke(TypingRequest $request, Conversation $conversation)
    {
        broadcast(new UserTyping($conversation, $request->user()))->toOthers();

        return ApiResponse::success(message: 'Typing event broadcasted.');
    }
}
