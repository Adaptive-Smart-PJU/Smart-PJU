<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LampController;
use App\Http\Controllers\OperationParameterController;
use App\Http\Controllers\TelemetryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/monitoring/status', [DashboardController::class, 'monitoringStatus'])->name('monitoring.status');

    Route::get('/lamps', [LampController::class, 'index'])->name('lamps.index');
    Route::get('/lamps/{lamp}', [LampController::class, 'show'])->name('lamps.show');

    Route::get('/operation-parameters', [OperationParameterController::class, 'index'])->name('operation-parameters.index');
    Route::get('/operation-parameters/{operationParameter}', [OperationParameterController::class, 'show'])->name('operation-parameters.show');

    Route::post('/telemetry/simulate', [TelemetryController::class, 'store'])->name('telemetry.simulate');
    Route::patch('/lamps/{lamp}/power-status', [TelemetryController::class, 'updatePowerStatus'])->name('lamps.power-status');
    Route::patch('/lamps/{lamp}/intensity', [TelemetryController::class, 'updateIntensity'])->name('lamps.intensity');

    Route::middleware('role:pengelola')->group(function (): void {
        Route::post('/lamps', [LampController::class, 'store'])->name('lamps.store');
        Route::put('/lamps/{lamp}', [LampController::class, 'update'])->name('lamps.update');
        Route::patch('/lamps/{lamp}/deactivate', [LampController::class, 'deactivate'])->name('lamps.deactivate');
        Route::delete('/lamps/{lamp}', [LampController::class, 'destroy'])->name('lamps.destroy');

        Route::post('/operation-parameters', [OperationParameterController::class, 'store'])->name('operation-parameters.store');
        Route::put('/operation-parameters/{operationParameter}', [OperationParameterController::class, 'update'])->name('operation-parameters.update');
        Route::delete('/operation-parameters/{operationParameter}', [OperationParameterController::class, 'destroy'])->name('operation-parameters.destroy');
    });
});
