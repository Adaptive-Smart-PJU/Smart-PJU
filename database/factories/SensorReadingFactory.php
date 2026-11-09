<?php

namespace Database\Factories;

use App\Models\Lamp;
use App\Models\SensorReading;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SensorReading>
 */
class SensorReadingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lamp_id' => Lamp::factory(),
            'gateway_id' => 'GW-TELU-01',
            'lux_value' => 15.0,
            'activity_detected' => true,
            'voltage_volt' => 220.0,
            'current_ampere' => 0.450,
            'power_watt' => 99.0,
            'power_status' => 'ON',
            'intensity_percent' => 100,
            'recorded_at' => now(),
        ];
    }
}
