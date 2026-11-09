<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Lamp;
use App\Models\OperationParameter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelemetryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ingests_sensor_reading_payload_from_gateway_and_updates_lamp(): void
    {
        $lamp = Lamp::factory()->turnedOff()->create([
            'controller_device_id' => 'CTRL-GW-101',
        ]);
        OperationParameter::factory()->create([
            'lamp_id' => $lamp->id,
            'lux_threshold' => 25.0,
            'hysteresis_lux' => 15.0,
            'dim_intensity_percent' => 30,
            'bright_intensity_percent' => 100,
        ]);

        $response = $this->postJson(route('api.telemetry.store'), [
            'controller_device_id' => 'CTRL-GW-101',
            'gateway_id' => 'GW-TELU-01',
            'lux_value' => 10.5,
            'activity_detected' => true,
            'voltage_volt' => 220.0,
            'current_ampere' => 0.450,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.power_status', 'ON')
            ->assertJsonPath('data.intensity_percent', 100);

        $this->assertDatabaseHas('sensor_readings', [
            'lamp_id' => $lamp->id,
            'gateway_id' => 'GW-TELU-01',
            'lux_value' => 10.5,
            'activity_detected' => true,
        ]);
        $this->assertDatabaseHas('lamps', [
            'id' => $lamp->id,
            'power_status' => 'ON',
            'intensity_percent' => 100,
        ]);
    }

    public function test_updates_lamp_power_status_automatically_using_lux_threshold_hysteresis(): void
    {
        $lamp = Lamp::factory()->turnedOff()->create();
        OperationParameter::factory()->create([
            'lamp_id' => $lamp->id,
            'lux_threshold' => 25.0,
            'hysteresis_lux' => 15.0,
        ]);

        $turnOnResponse = $this->patchJson(route('api.lamps.power-status', $lamp), [
            'lux_value' => 12.0,
        ]);

        $turnOnResponse->assertOk()
            ->assertJsonPath('data.power_status', 'ON');

        $hysteresisHoldResponse = $this->patchJson(route('api.lamps.power-status', $lamp), [
            'lux_value' => 32.0,
        ]);

        $hysteresisHoldResponse->assertOk()
            ->assertJsonPath('data.power_status', 'ON');

        $turnOffResponse = $this->patchJson(route('api.lamps.power-status', $lamp), [
            'lux_value' => 55.0,
        ]);

        $turnOffResponse->assertOk()
            ->assertJsonPath('data.power_status', 'OFF')
            ->assertJsonPath('data.intensity_percent', 0);
    }

    public function test_updates_lamp_intensity_adaptively_based_on_activity_detection(): void
    {
        $lamp = Lamp::factory()->create([
            'power_status' => 'ON',
            'intensity_percent' => 100,
        ]);
        OperationParameter::factory()->create([
            'lamp_id' => $lamp->id,
            'dim_intensity_percent' => 30,
            'bright_intensity_percent' => 100,
        ]);

        $dimResponse = $this->patchJson(route('api.lamps.intensity', $lamp), [
            'activity_detected' => false,
        ]);

        $dimResponse->assertOk()
            ->assertJsonPath('data.intensity_percent', 30)
            ->assertJsonPath('data.activity_detected', false);

        $brightResponse = $this->patchJson(route('api.lamps.intensity', $lamp), [
            'activity_detected' => true,
        ]);

        $brightResponse->assertOk()
            ->assertJsonPath('data.intensity_percent', 100)
            ->assertJsonPath('data.activity_detected', true);
    }
}
