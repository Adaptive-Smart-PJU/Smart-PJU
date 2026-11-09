<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Lamp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Lamp
 */
class LampResource extends JsonResource
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
            'lamp_code' => $this->lamp_code,
            'name' => $this->name,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'controller_device_id' => $this->controller_device_id,
            'is_active' => $this->is_active,
            'power_status' => $this->power_status,
            'operation_mode' => $this->operation_mode,
            'intensity_percent' => $this->intensity_percent,
            'connectivity_status' => $this->connectivity_status,
            'lux_value' => $this->lux_value,
            'activity_detected' => $this->activity_detected,
            'voltage_volt' => $this->voltage_volt,
            'current_ampere' => $this->current_ampere,
            'power_watt' => $this->power_watt,
            'last_seen_at' => $this->last_seen_at?->toIso8601String(),
            'operation_parameter' => new OperationParameterResource($this->whenLoaded('operationParameter')),
            'latest_reading' => new SensorReadingResource($this->whenLoaded('latestReading')),
        ];
    }
}
