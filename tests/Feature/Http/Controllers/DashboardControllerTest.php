<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Lamp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_renders_realtime_monitoring_dashboard_with_lamp_conditions(): void
    {
        $user = User::factory()->pengelola()->create();
        $lamp = Lamp::factory()->create([
            'lamp_code' => 'PJU-MON-001',
            'name' => 'Tiang Monitoring Utama',
            'lux_value' => 14.5,
            'power_watt' => 98.5,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('PJU-MON-001')
            ->assertSee('Tiang Monitoring Utama')
            ->assertSee((string) $lamp->controller_device_id);
    }

    public function test_returns_json_realtime_monitoring_status_payload(): void
    {
        $user = User::factory()->pengelola()->create();
        Lamp::factory()->count(2)->create([
            'power_status' => 'ON',
            'power_watt' => 50.0,
        ]);

        $response = $this->actingAs($user)->getJson(route('monitoring.status'));

        $response->assertOk()
            ->assertJsonPath('meta.total_lamps', 2)
            ->assertJsonPath('meta.lamps_on', 2);
    }
}
