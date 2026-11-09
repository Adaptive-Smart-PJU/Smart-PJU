<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTelemetryRequest;
use App\Http\Requests\UpdateLampIntensityRequest;
use App\Http\Requests\UpdateLampPowerStatusRequest;
use App\Http\Resources\LampResource;
use App\Http\Resources\SensorReadingResource;
use App\Models\Lamp;
use App\Services\SmartLightingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class TelemetryController extends Controller
{
    public function __construct(
        public readonly SmartLightingService $lightingService,
    ) {}

    /**
     * Ingest sensor reading payload from IoT Gateway (KAN-38 / KF-03).
     */
    public function store(StoreTelemetryRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $lamp = isset($validated['lamp_id'])
            ? Lamp::findOrFail((int) $validated['lamp_id'])
            : Lamp::where('controller_device_id', (string) $validated['controller_device_id'])->firstOrFail();

        $reading = $this->lightingService->ingestGatewayTelemetry($lamp, $validated);

        if ($request->expectsJson() || $request->is('api/*')) {
            return (new SensorReadingResource($reading->load('lamp')))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->route('dashboard')
            ->with('status', "Data telemetri sensor untuk {$lamp->lamp_code} berhasil disimpan melalui Gateway.");
    }

    /**
     * Update lamp ON/OFF status based on ambient light threshold with hysteresis (KAN-44 / KF-04).
     */
    public function updatePowerStatus(
        UpdateLampPowerStatusRequest $request,
        Lamp $lamp,
    ): RedirectResponse|JsonResponse {
        $lamp->loadMissing('operationParameter');

        $luxValue = (float) $request->validated('lux_value');
        $evaluatedStatus = $request->validated('power_status')
            ?? $this->lightingService->evaluatePowerStatusByLux($lamp, $luxValue);

        $intensityPercent = $evaluatedStatus === 'ON'
            ? $this->lightingService->evaluateAdaptiveIntensity($lamp, (bool) $lamp->activity_detected, 'ON')
            : 0;

        $currentAmpere = $evaluatedStatus === 'ON'
            ? round(0.45 * ($intensityPercent / 100), 3)
            : 0.0;

        $lamp->update([
            'lux_value' => $luxValue,
            'power_status' => $evaluatedStatus,
            'intensity_percent' => $intensityPercent,
            'current_ampere' => $currentAmpere,
            'power_watt' => round((float) $lamp->voltage_volt * $currentAmpere, 2),
            'connectivity_status' => 'ONLINE',
            'last_seen_at' => now(),
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return (new LampResource($lamp->refresh()->load('operationParameter')))->response();
        }

        return redirect()->route('dashboard')
            ->with('status', "Status lampu {$lamp->lamp_code} diperbarui menjadi {$evaluatedStatus} (Lux: {$luxValue}).");
    }

    /**
     * Update adaptive PWM dimming intensity percentage based on activity detection (KAN-45 / KF-05).
     */
    public function updateIntensity(
        UpdateLampIntensityRequest $request,
        Lamp $lamp,
    ): RedirectResponse|JsonResponse {
        $lamp->loadMissing('operationParameter');

        $activityDetected = $request->boolean('activity_detected');
        $intensityPercent = $request->validated('intensity_percent') !== null
            ? (int) $request->validated('intensity_percent')
            : $this->lightingService->evaluateAdaptiveIntensity($lamp, $activityDetected);

        $currentAmpere = $lamp->power_status === 'ON'
            ? round(0.45 * ($intensityPercent / 100), 3)
            : 0.0;

        $lamp->update([
            'activity_detected' => $activityDetected,
            'intensity_percent' => $intensityPercent,
            'current_ampere' => $currentAmpere,
            'power_watt' => round((float) $lamp->voltage_volt * $currentAmpere, 2),
            'connectivity_status' => 'ONLINE',
            'last_seen_at' => now(),
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return (new LampResource($lamp->refresh()->load('operationParameter')))->response();
        }

        return redirect()->route('dashboard')
            ->with('status', "Intensitas adaptif lampu {$lamp->lamp_code} diatur ke {$intensityPercent}%.");
    }
}
