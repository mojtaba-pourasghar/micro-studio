<?php

use App\Http\Controllers\Api\AgentMediaController;
use App\Http\Middleware\AuthenticateAgent;
use Illuminate\Support\Facades\Route;

Route::prefix('agent')->middleware([AuthenticateAgent::class, 'throttle:120,1'])->group(function () {
    Route::get('/ping', [AgentMediaController::class, 'ping']);
    Route::post('/media', [AgentMediaController::class, 'store']);
});
