<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Lamp;
use App\Models\OperationParameter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationParameterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengelola_creates_new_operation_parameter_profile(): void
    {
        $pengelola = User::factory()->pengelola()->create();
        $lamp = Lamp::factory()->create();

        $response = $this->actingAs($pengelola)->post(route('operation-parameters.store'), [
            'lamp_id' => $lamp->id,
            'profile_name' => 'Profil Hemat Kampus',
            'lux_threshold' => 20.0,
            'hysteresis_lux' => 12.0,
            'dim_intensity_percent' => 25,
            'bright_intensity_percent' => 100,
            'energy_saving_start_time' => '21:30',
            'energy_saving_end_time' => '05:00',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('operation_parameters', [
            'lamp_id' => $lamp->id,
            'profile_name' => 'Profil Hemat Kampus',
            'lux_threshold' => 20.0,
            'dim_intensity_percent' => 25,
            'is_custom' => true,
        ]);
    }

    public function test_returns_active_operation_parameter_details(): void
    {
        $pengelola = User::factory()->pengelola()->create();
        $parameter = OperationParameter::factory()->create([
            'profile_name' => 'Profil Koridor Utama',
            'lux_threshold' => 28.0,
        ]);

        $response = $this->actingAs($pengelola)->getJson(route('operation-parameters.show', $parameter));

        $response->assertOk()
            ->assertJsonPath('data.profile_name', 'Profil Koridor Utama')
            ->assertJsonPath('data.lux_threshold', 28);
    }

    public function test_pengelola_updates_operation_parameters_from_dashboard(): void
    {
        $pengelola = User::factory()->pengelola()->create();
        $parameter = OperationParameter::factory()->create();

        $response = $this->actingAs($pengelola)->put(route('operation-parameters.update', $parameter), [
            'profile_name' => 'Profil Lapangan Diperbarui',
            'lux_threshold' => 32.0,
            'hysteresis_lux' => 15.0,
            'dim_intensity_percent' => 35,
            'bright_intensity_percent' => 95,
            'energy_saving_start_time' => '23:00',
            'energy_saving_end_time' => '04:30',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('operation_parameters', [
            'id' => $parameter->id,
            'profile_name' => 'Profil Lapangan Diperbarui',
            'lux_threshold' => 32.0,
            'dim_intensity_percent' => 35,
            'bright_intensity_percent' => 95,
        ]);
    }

    public function test_pengelola_resets_custom_operation_parameter_to_safe_default(): void
    {
        $pengelola = User::factory()->pengelola()->create();
        $parameter = OperationParameter::factory()->create([
            'profile_name' => 'Profil Kustom Ekstrem',
            'lux_threshold' => 80.0,
            'dim_intensity_percent' => 10,
            'is_custom' => true,
        ]);

        $response = $this->actingAs($pengelola)->delete(route('operation-parameters.destroy', $parameter));

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('operation_parameters', [
            'id' => $parameter->id,
            'profile_name' => 'Profil Bawaan Aman (Default)',
            'lux_threshold' => 25.0,
            'dim_intensity_percent' => 30,
            'bright_intensity_percent' => 100,
            'is_custom' => false,
        ]);
    }
}
