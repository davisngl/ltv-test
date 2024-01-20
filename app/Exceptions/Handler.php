<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Fluent;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e) {
            if (app()->runningUnitTests() || ! request()->wantsJson()) {
                // In tests, some assertions require the "original" structure of certain exceptions,
                // therefore, we only would overwrite the structure during normal operation.
                return;
            }

            return response()->failure(
                message: $e->getMessage(),
                status: (new Fluent($e))->status ?: 404 // fail-safe checking if 'status' property is set or not
            );
        });
    }

    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return response()->failure(
            message: $exception->getMessage(),
            data: count($exception->errors())
                ? $exception->errors()
                : null,
            status: $exception->status);
    }
}
