<?php

use App\Http\Controllers\GuideController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('guide/{channel}/{date}', [GuideController::class, 'guideForDay'])
        ->name('guide-for-day');
    Route::get('on-air/{channel}', [GuideController::class, 'currentBroadcast'])
        ->name('on-air');
    Route::get('upcoming/{channel}', [GuideController::class, 'upcomingBroadcasts'])
        ->name('upcoming-broadcasts');
    Route::post('compose-guide', [GuideController::class, 'composeGuide'])
        ->name('compose-guide');
});
