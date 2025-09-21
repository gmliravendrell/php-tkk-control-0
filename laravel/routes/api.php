<?php

use App\Http\Controllers\Api\CheckController;
use App\Http\Controllers\Api\ControlController;
use App\Http\Controllers\Api\ParticipantController;
use Illuminate\Support\Facades\Route;

Route::apiResource('controls', ControlController::class);
Route::apiResource('participants', ParticipantController::class);
Route::post('/checks', [CheckController::class, 'store'])->name('checks.store');
Route::post('admin/participants', [ParticipantController::class, 'import']);
Route::post('admin/controls', [ControlController::class, 'import']);
Route::get('/controls/{control}/checks', [CheckController::class, 'getChecksByControl']);
Route::get('/checks/abandons', [CheckController::class, 'getAbandons']);
