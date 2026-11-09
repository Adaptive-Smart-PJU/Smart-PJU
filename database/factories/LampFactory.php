<?php

namespace Database\Factories;

use App\Models\Lamp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lamp>
 */
class LampFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lamp_code' => 'PJU-'.fake()->unique()->numerify('###'),
            'name' => 'Tiang PJU '.fake()->streetName(),
            'location' => fake()->streetAddress().', Bandung',
            'latitude' => fake()->latitude(-6.9800, -6.9680),
            'longitude' => fake()->longitude(107.6250, 107.6380),
            'controller_device_id' => 'CTRL-NODE-'.fake()->unique()->numerify('###'),
            'is_active' => true,
            'power_status' => 'ON',
            'operation_mode' => 'ADAPTIVE',
            'intensity_percent' => 100,
            'connectivity_status' => 'ONLINE',
            'lux_value' => 12.5,
            'activity_detected' => true,
            'voltage_volt' => 220.0,
            'current_ampere' => 0.450,
            'power_watt' => 99.0,
            'last_seen_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'power_status' => 'OFF',
            'intensity_percent' => 0,
        ]);
    }

    public function turnedOff(): static
    {
        return $this->state(fn (array $attributes) => [
            'power_status' => 'OFF',
            'intensity_percent' => 0,
        ]);
    }
}
