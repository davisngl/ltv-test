<?php

namespace App\Providers;

use App\Services\CompilableGuideInterface;
use App\Services\Guide;
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
        $this->app->bind(CompilableGuideInterface::class, Guide::class);
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
        Response::macro('success', function (string $message = 'Success', mixed $data = [], int $status = ResponseCode::HTTP_OK) {
            return Response::json([
                'success' => true,
                'message' => $message,
                'data'    => $data,
            ], $status);
        });

        Response::macro('failure', function (string $message = 'Failure', mixed $data = [], int $status = ResponseCode::HTTP_UNPROCESSABLE_ENTITY) {
            return Response::json([
                'success' => false,
                'message' => $message,
                'data'    => $data,
            ], $status);
        });
    }
}
