<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class NotFollowingException extends Exception
{
    public function render($request): JsonResponse
    {
        return ApiResponse::error(message: 'You are not following this user.');
    }
}
