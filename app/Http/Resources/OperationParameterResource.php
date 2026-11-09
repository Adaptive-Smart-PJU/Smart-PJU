<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\OperationParameter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OperationParameter
 */
class OperationParameterResource extends JsonResource
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
            'profile_name' => $this->profile_name,
            'lux_threshold' => $this->lux_threshold,
            'hysteresis_lux' => $this->hysteresis_lux,
            'dim_intensity_percent' => $this->dim_intensity_percent,
            'bright_intensity_percent' => $this->bright_intensity_percent,
            'energy_saving_start_time' => $this->energy_saving_start_time,
            'energy_saving_end_time' => $this->energy_saving_end_time,
            'is_custom' => $this->is_custom,
            'is_active' => $this->is_active,
            'synced_to_gateway_at' => $this->synced_to_gateway_at?->toIso8601String(),
        ];
    }
}
