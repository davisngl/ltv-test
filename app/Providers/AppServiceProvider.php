<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response as ResponseCode;
use Illuminate\Support\Collection;
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
        Response::macro('success', function (string $message = 'Success', Collection|array $data = [], int $status = ResponseCode::HTTP_OK) {
            return Response::json([
                'success' => true,
                'message' => $message,
                'data'    => count($data) ? $data : null,
            ], $status);
        });

        Response::macro('failure', function (string $message = 'Failure', Collection|array $data = [], int $status = ResponseCode::HTTP_UNPROCESSABLE_ENTITY) {
            $data = $data instanceof Collection ? $data->toArray() : $data;

            return Response::json([
                'success' => false,
                'message' => $message,
                'data'    => count($data) ? $data : null,
            ], $status);
        });
    }
}
