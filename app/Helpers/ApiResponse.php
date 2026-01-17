<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($message = '', $data = [], $status = 200)
    {
        return response()->json([
            'success' => 'true',
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public static function error($message = '', $data = [], $status = 400)
    {
        return response()->json([
            'success' => 'false',
            'message' => $message,
            'data' => $data
        ], $status);
    }
}
