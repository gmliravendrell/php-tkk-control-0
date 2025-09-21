<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ControlController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QrScannerController;
use App\Http\Controllers\AdminPanelController;
use App\Http\Controllers\CentralController;
use App\Http\Controllers\CentralReportController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/dashboard', function () {
    return "Pantalla de Dashboard (la desarrollaremos luego)";
})->name('dashboard');
Route::get('/control', [ControlController::class, 'index'])->name('control.index');
Route::get('/control/{id}', [ControlController::class, 'show'])->name('control.show');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/scanner/{id}', [QrScannerController::class, 'index'])->name('scanner.index');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/admin', [AdminPanelController::class, 'index'])->name('admin.index');
Route::get('/central', [CentralController::class, 'index'])->name('central.index');
Route::get('/report', [CentralReportController::class, 'index'])->name('central.report');