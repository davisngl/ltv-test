<?php

use App\Http\Controllers\GuideController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->controller(GuideController::class)
    ->group(function () {
        Route::get('guide/{channel:number}/{date}', 'guideForDay')
            ->name('guide-for-day');
        Route::get('on-air/{channel:number}', 'currentBroadcast')
            ->name('on-air');
        Route::get('upcoming/{channel:number}', 'upcomingBroadcasts')
            ->name('upcoming-broadcasts');
        Route::post('compose-guide', 'composeGuide')
            ->name('compose-guide');
    });
