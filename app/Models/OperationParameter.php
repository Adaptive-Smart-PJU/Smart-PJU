<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OperationParameterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lamp_id',
    'profile_name',
    'lux_threshold',
    'hysteresis_lux',
    'dim_intensity_percent',
    'bright_intensity_percent',
    'energy_saving_start_time',
    'energy_saving_end_time',
    'is_custom',
    'is_active',
    'synced_to_gateway_at',
])]
class OperationParameter extends Model
{
    /** @use HasFactory<OperationParameterFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'lux_threshold' => 25.00,
        'hysteresis_lux' => 15.00,
        'dim_intensity_percent' => 30,
        'bright_intensity_percent' => 100,
        'energy_saving_start_time' => '22:00',
        'energy_saving_end_time' => '05:00',
        'is_custom' => true,
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lux_threshold' => 'float',
            'hysteresis_lux' => 'float',
            'dim_intensity_percent' => 'integer',
            'bright_intensity_percent' => 'integer',
            'is_custom' => 'boolean',
            'is_active' => 'boolean',
            'synced_to_gateway_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Lamp, $this>
     */
    public function lamp(): BelongsTo
    {
        return $this->belongsTo(Lamp::class);
    }

    /**
     * @return array<string, mixed>
     */
    public static function safeDefaultAttributes(): array
    {
        return [
            'profile_name' => 'Profil Bawaan Aman (Default)',
            'lux_threshold' => 25.00,
            'hysteresis_lux' => 15.00,
            'dim_intensity_percent' => 30,
            'bright_intensity_percent' => 100,
            'energy_saving_start_time' => '22:00',
            'energy_saving_end_time' => '05:00',
            'is_custom' => false,
            'is_active' => true,
        ];
    }
}
