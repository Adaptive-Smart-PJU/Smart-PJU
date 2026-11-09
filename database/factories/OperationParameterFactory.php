<?php

namespace Database\Factories;

use App\Models\Lamp;
use App\Models\OperationParameter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperationParameter>
 */
class OperationParameterFactory extends Factory
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
            'profile_name' => 'Profil Operasi '.fake()->words(2, true),
            'lux_threshold' => 25.0,
            'hysteresis_lux' => 15.0,
            'dim_intensity_percent' => 30,
            'bright_intensity_percent' => 100,
            'energy_saving_start_time' => '22:00',
            'energy_saving_end_time' => '05:00',
            'is_custom' => true,
            'is_active' => true,
            'synced_to_gateway_at' => now(),
        ];
    }

    public function defaultProfile(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_name' => 'Profil Bawaan Aman (Default)',
            'is_custom' => false,
        ]);
    }
}
