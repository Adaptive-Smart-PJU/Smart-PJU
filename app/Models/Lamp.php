<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LampFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'lamp_code',
    'name',
    'location',
    'latitude',
    'longitude',
    'controller_device_id',
    'is_active',
    'power_status',
    'operation_mode',
    'intensity_percent',
    'connectivity_status',
    'lux_value',
    'activity_detected',
    'voltage_volt',
    'current_ampere',
    'power_watt',
    'last_seen_at',
])]
class Lamp extends Model
{
    /** @use HasFactory<LampFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
        'power_status' => 'OFF',
        'operation_mode' => 'ADAPTIVE',
        'intensity_percent' => 0,
        'connectivity_status' => 'ONLINE',
        'lux_value' => 0.00,
        'activity_detected' => false,
        'voltage_volt' => 220.00,
        'current_ampere' => 0.000,
        'power_watt' => 0.00,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_active' => 'boolean',
            'intensity_percent' => 'integer',
            'lux_value' => 'float',
            'activity_detected' => 'boolean',
            'voltage_volt' => 'float',
            'current_ampere' => 'float',
            'power_watt' => 'float',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * @return HasOne<OperationParameter, $this>
     */
    public function operationParameter(): HasOne
    {
        return $this->hasOne(OperationParameter::class)->latestOfMany();
    }

    /**
     * @return HasMany<OperationParameter, $this>
     */
    public function operationParameters(): HasMany
    {
        return $this->hasMany(OperationParameter::class);
    }

    /**
     * @return HasMany<SensorReading, $this>
     */
    public function sensorReadings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }

    /**
     * @return HasOne<SensorReading, $this>
     */
    public function latestReading(): HasOne
    {
        return $this->hasOne(SensorReading::class)->latestOfMany('recorded_at');
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
