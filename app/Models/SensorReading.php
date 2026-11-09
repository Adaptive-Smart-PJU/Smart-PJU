<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SensorReadingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lamp_id',
    'gateway_id',
    'lux_value',
    'activity_detected',
    'voltage_volt',
    'current_ampere',
    'power_watt',
    'power_status',
    'intensity_percent',
    'recorded_at',
])]
class SensorReading extends Model
{
    /** @use HasFactory<SensorReadingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lux_value' => 'float',
            'activity_detected' => 'boolean',
            'voltage_volt' => 'float',
            'current_ampere' => 'float',
            'power_watt' => 'float',
            'intensity_percent' => 'integer',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Lamp, $this>
     */
    public function lamp(): BelongsTo
    {
        return $this->belongsTo(Lamp::class);
    }
}
