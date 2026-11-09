<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Lamp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LampControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengelola_creates_new_lamp_with_default_operation_parameter(): void
    {
        $pengelola = User::factory()->pengelola()->create();

        $response = $this->actingAs($pengelola)->post(route('lamps.store'), [
            'lamp_code' => 'PJU-NEW-001',
            'name' => 'Tiang PJU Gedung Bangkit',
            'location' => 'Jl. Telekomunikasi No. 1, Bandung',
            'latitude' => -6.9731000,
            'longitude' => 107.6304000,
            'controller_device_id' => 'CTRL-NEW-001',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('lamps', [
            'lamp_code' => 'PJU-NEW-001',
            'controller_device_id' => 'CTRL-NEW-001',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('operation_parameters', [
            'profile_name' => 'Profil Bawaan Aman (Default)',
        ]);
    }

    public function test_forbids_teknisi_from_creating_or_modifying_lamps(): void
    {
        $teknisi = User::factory()->teknisi()->create();

        $response = $this->actingAs($teknisi)->post(route('lamps.store'), [
            'lamp_code' => 'PJU-FORBID-01',
            'name' => 'Tiang Terlarang',
            'location' => 'Bandung',
            'latitude' => -6.97,
            'longitude' => 107.63,
            'controller_device_id' => 'CTRL-FORBID-01',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('lamps', [
            'lamp_code' => 'PJU-FORBID-01',
        ]);
    }

    public function test_lists_and_shows_lamp_details_with_controller_identity(): void
    {
        $pengelola = User::factory()->pengelola()->create();
        $lamp = Lamp::factory()->create([
            'lamp_code' => 'PJU-DET-001',
            'location' => 'Simpang Sukabirus',
            'controller_device_id' => 'CTRL-DET-001',
        ]);

        $listResponse = $this->actingAs($pengelola)->get(route('lamps.index', ['search' => 'PJU-DET-001']));
        $detailResponse = $this->actingAs($pengelola)->get(route('lamps.show', $lamp));

        $listResponse->assertOk()->assertSee('PJU-DET-001')->assertSee('CTRL-DET-001');
        $detailResponse->assertOk()->assertSee('Simpang Sukabirus')->assertSee('CTRL-DET-001');
    }

    public function test_pengelola_updates_existing_lamp_data(): void
    {
        $pengelola = User::factory()->pengelola()->create();
        $lamp = Lamp::factory()->create([
            'lamp_code' => 'PJU-UPD-001',
        ]);

        $response = $this->actingAs($pengelola)->put(route('lamps.update', $lamp), [
            'lamp_code' => 'PJU-UPD-001',
            'name' => 'Tiang PJU Nama Baru',
            'location' => 'Lokasi Baru Sukapura',
            'latitude' => -6.9740000,
            'longitude' => 107.6310000,
            'controller_device_id' => 'CTRL-UPD-999',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('lamps', [
            'id' => $lamp->id,
            'name' => 'Tiang PJU Nama Baru',
            'location' => 'Lokasi Baru Sukapura',
            'controller_device_id' => 'CTRL-UPD-999',
        ]);
    }

    public function test_pengelola_deactivates_and_deletes_lamp(): void
    {
        $pengelola = User::factory()->pengelola()->create();
        $lamp = Lamp::factory()->create([
            'is_active' => true,
            'power_status' => 'ON',
            'intensity_percent' => 100,
        ]);

        $deactivateResponse = $this->actingAs($pengelola)->patch(route('lamps.deactivate', $lamp));

        $deactivateResponse->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('lamps', [
            'id' => $lamp->id,
            'is_active' => false,
            'power_status' => 'OFF',
            'intensity_percent' => 0,
        ]);

        $deleteResponse = $this->actingAs($pengelola)->delete(route('lamps.destroy', $lamp));

        $deleteResponse->assertRedirect(route('dashboard'));
        $this->assertDatabaseMissing('lamps', [
            'id' => $lamp->id,
        ]);
    }
}
