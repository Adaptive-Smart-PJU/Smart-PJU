<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LampController;
use App\Http\Controllers\OperationParameterController;
use App\Http\Controllers\TelemetryController;
use Illuminate\Support\Facades\Route;

Route::get('/monitoring/status', [DashboardController::class, 'monitoringStatus'])->name('api.monitoring.status');
Route::get('/lamps', [LampController::class, 'index'])->name('api.lamps.index');
Route::get('/lamps/{lamp}', [LampController::class, 'show'])->name('api.lamps.show');

Route::get('/operation-parameters', [OperationParameterController::class, 'index'])->name('api.operation-parameters.index');
Route::get('/operation-parameters/{operationParameter}', [OperationParameterController::class, 'show'])->name('api.operation-parameters.show');

Route::post('/telemetry', [TelemetryController::class, 'store'])->name('api.telemetry.store');
Route::patch('/lamps/{lamp}/power-status', [TelemetryController::class, 'updatePowerStatus'])->name('api.lamps.power-status');
Route::patch('/lamps/{lamp}/intensity', [TelemetryController::class, 'updateIntensity'])->name('api.lamps.intensity');
