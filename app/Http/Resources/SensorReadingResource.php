<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SensorReading
 */
class SensorReadingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lamp_id' => $this->lamp_id,
            'gateway_id' => $this->gateway_id,
            'lux_value' => $this->lux_value,
            'activity_detected' => $this->activity_detected,
            'voltage_volt' => $this->voltage_volt,
            'current_ampere' => $this->current_ampere,
            'power_watt' => $this->power_watt,
            'power_status' => $this->power_status,
            'intensity_percent' => $this->intensity_percent,
            'recorded_at' => $this->recorded_at?->toIso8601String(),
        ];
    }
}
