<?php

namespace App\Exceptions\Renderers;

use Throwable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiExceptionRenderer
{
    public function handle(Throwable $e, Request $request): Response|null
    {
        // Перевіряємо чи це API-запит
        if ($request->expectsJson() || $request->is('api/*')) {

            $status = 500;
            $message = $e->getMessage();
            $errors = null;

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $status = 422;
                $message = 'Validation error';
                $errors = $e->errors();
            }

            elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                $status = 404;
                $message = 'Route not found';
            }

            elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                $status = 405;
                $message = 'Method not allowed';
            }

            return response()->json([
                'status'  => 'error',
                'message' => $message ?: 'Server Error',
                'errors'  => $errors,
            ], $status);
        }

        // Для веб — пропускаємо і даємо іншим рендерерам обробити HTML
        return null;
    }
}
