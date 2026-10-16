<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Exceptions\SocialAuthException;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GoogleCallbackRequest;
use App\Http\Resources\User\UserResource;
use App\Services\AuthenticationService;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends Controller
{
    public function __construct(
        protected AuthenticationService $authService
    ) {
    }

    public function redirectToGoogle(): JsonResponse
    {
        try {
            return ApiResponse::success(
                message: 'Google redirect url generated',
                data: $this->authService->redirectToGoogle()
            );
        } catch (Exception $e) {
            Log::error('Error generating Google redirect: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Failed to start Google authentication', status: 500);
        }
    }

    public function handleGoogleCallback(GoogleCallbackRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->handleGoogleCallback();

            return ApiResponse::success(
                message: 'User successfully logged in',
                data: [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                    'token_type' => $result['token_type'],
                    'expires_at' => $result['expires_at'],
                    'is_new_user' => $result['is_new_user'],
                ]
            );
        } catch (SocialAuthException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                status: $e->status,
                errors: [$e->field => [$e->getMessage()]]
            );
        } catch (ClientException) {
            $message = 'The Google code is invalid or has expired.';

            return ApiResponse::error(message: $message, status: 422, errors: ['code' => [$message]]);
        } catch (Exception $e) {
            Log::error('Error handling Google callback: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(message: 'Failed to authenticate with Google', status: 500);
        }
    }
}
