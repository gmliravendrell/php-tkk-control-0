<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ControlController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QrScannerController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/dashboard', function () {
    return "Pantalla de Dashboard (la desarrollaremos luego)";
})->name('dashboard');
Route::get('/control', [ControlController::class, 'index'])->name('control.index');
Route::get('/control/{id}', [ControlController::class, 'show'])->name('control.show');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/scanner/{id}', [QrScannerController::class, 'index'])->name('scanner.index');
