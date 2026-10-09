<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        ?string $message = null,
        mixed $data = null,
        int $status = 200,
        ?array $meta = null
    ): JsonResponse {
        $body = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null) {
            $body['meta'] = $meta;
        }

        return response()->json($body, $status);
    }

    public static function error(
        ?string $message = null,
        mixed $data = null,
        int $status = 400,
        ?array $errors = null
    ): JsonResponse {
        $body = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        if ($data !== null) {
            $body['data'] = $data;
        }

        return response()->json($body, $status);
    }
}
