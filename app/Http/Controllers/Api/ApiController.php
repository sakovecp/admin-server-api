<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Server API Документація",
 *     description="API для управління сервером та віртуальними хостами"
 * )
 *
 * @OA\Server(
 *     url="/api/v1",
 *     description="API v1"
 * )
 */
class ApiController extends Controller
{

    /**
     * Success response wrapper.
     */
    protected function success($data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Error response wrapper.
     */
    protected function error(string $message = 'Error', int $status = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }
}
