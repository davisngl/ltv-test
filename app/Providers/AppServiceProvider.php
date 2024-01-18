<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response as ResponseCode;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(
            $this->app->environment(['local', 'development', 'staging'])
        );

        // API response macros
        Response::macro('success', function (string $message, array $data = [], int $status = ResponseCode::HTTP_OK) {
            return Response::json([
                'success' => true,
                'message' => $message ?: 'Success',
                'data'    => count($data) ? $data : null,
            ], $status);
        });

        Response::macro('failure', function (string $message, array $data = [], int $status = ResponseCode::HTTP_UNPROCESSABLE_ENTITY) {
            return Response::json([
                'success' => false,
                'message' => $message ?: 'Failure',
                'data'    => count($data) ? $data : null,
            ], $status);
        });
    }
}
