<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lamp;
use App\Models\OperationParameter;
use App\Models\SensorReading;

class SmartLightingService
{
    /**
     * Evaluate hysteresis light threshold to determine ON/OFF status (KAN-44 / KF-04).
     */
    public function evaluatePowerStatusByLux(Lamp $lamp, float $luxValue): string
    {
        $parameter = $lamp->operationParameter;
        $darkThreshold = $parameter !== null ? (float) $parameter->lux_threshold : 25.0;
        $hysteresis = $parameter !== null ? (float) $parameter->hysteresis_lux : 15.0;
        $brightThreshold = $darkThreshold + $hysteresis;

        if ($luxValue < $darkThreshold) {
            return 'ON';
        }

        if ($luxValue > $brightThreshold) {
            return 'OFF';
        }

        return $lamp->power_status;
    }

    /**
     * Evaluate adaptive PWM dimming percentage based on activity detection (KAN-45 / KF-05).
     */
    public function evaluateAdaptiveIntensity(Lamp $lamp, bool $activityDetected, ?string $powerStatus = null): int
    {
        $effectivePower = $powerStatus ?? $lamp->power_status;

        if ($effectivePower === 'OFF' || ! $lamp->is_active) {
            return 0;
        }

        $parameter = $lamp->operationParameter;
        $dimPercent = $parameter !== null ? (int) $parameter->dim_intensity_percent : 30;
        $brightPercent = $parameter !== null ? (int) $parameter->bright_intensity_percent : 100;

        if ($activityDetected) {
            return $brightPercent;
        }

        return $dimPercent;
    }

    /**
     * Ingest sensor reading payload from Gateway and update lamp operational state (KAN-38, KAN-44, KAN-45).
     *
     * @param  array<string, mixed>  $payload
     */
    public function ingestGatewayTelemetry(Lamp $lamp, array $payload): SensorReading
    {
        $lamp->loadMissing('operationParameter');

        $luxValue = (float) ($payload['lux_value'] ?? $lamp->lux_value);
        $activityDetected = (bool) ($payload['activity_detected'] ?? false);
        $voltageVolt = (float) ($payload['voltage_volt'] ?? 220.0);
        $currentAmpere = (float) ($payload['current_ampere'] ?? 0.45);

        $powerStatus = isset($payload['power_status'])
            ? (string) $payload['power_status']
            : $this->evaluatePowerStatusByLux($lamp, $luxValue);

        $intensityPercent = isset($payload['intensity_percent'])
            ? (int) $payload['intensity_percent']
            : $this->evaluateAdaptiveIntensity($lamp, $activityDetected, $powerStatus);

        if ($powerStatus === 'OFF') {
            $intensityPercent = 0;
            $currentAmpere = 0.0;
        }

        $powerWatt = isset($payload['power_watt'])
            ? (float) $payload['power_watt']
            : round($voltageVolt * $currentAmpere, 2);

        $reading = SensorReading::create([
            'lamp_id' => $lamp->id,
            'gateway_id' => (string) ($payload['gateway_id'] ?? 'GW-TELU-01'),
            'lux_value' => $luxValue,
            'activity_detected' => $activityDetected,
            'voltage_volt' => $voltageVolt,
            'current_ampere' => $currentAmpere,
            'power_watt' => $powerWatt,
            'power_status' => $powerStatus,
            'intensity_percent' => $intensityPercent,
            'recorded_at' => now(),
        ]);

        $lamp->update([
            'lux_value' => $luxValue,
            'activity_detected' => $activityDetected,
            'voltage_volt' => $voltageVolt,
            'current_ampere' => $currentAmpere,
            'power_watt' => $powerWatt,
            'power_status' => $powerStatus,
            'intensity_percent' => $intensityPercent,
            'connectivity_status' => 'ONLINE',
            'last_seen_at' => now(),
        ]);

        return $reading;
    }

    /**
     * Reset a custom operation parameter profile back to safe defaults (KAN-43).
     */
    public function resetOperationParameterToDefault(OperationParameter $parameter): OperationParameter
    {
        $defaults = OperationParameter::safeDefaultAttributes();
        $defaults['synced_to_gateway_at'] = now();

        $parameter->update($defaults);

        return $parameter->refresh();
    }
}
