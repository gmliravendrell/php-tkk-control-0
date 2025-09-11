<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ControlController;
use App\Http\Controllers\Api\ParticipantController;
use App\Http\Controllers\Api\CheckController;

Route::apiResource('controls', ControlController::class);
Route::apiResource('participants', ParticipantController::class);
Route::post('/checks', [CheckController::class, 'store'])->name('checks.store');