<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Lamp;
use App\Models\OperationParameter;
use App\Models\SensorReading;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'pengelola@smartpju.id'],
            [
                'name' => 'Muhammad Habibie Kurniawan (Pengelola)',
                'role' => 'pengelola',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        User::updateOrCreate(
            ['email' => 'teknisi@smartpju.id'],
            [
                'name' => 'Tim Teknisi Lapangan PJU',
                'role' => 'teknisi',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $lampDefinitions = [
            [
                'lamp_code' => 'PJU-TELU-001',
                'name' => 'Tiang PJU Gerbang Utama Telkom University',
                'location' => 'Jl. Telekomunikasi No. 1, Terusan Buahbatu, Bandung',
                'latitude' => -6.9730500,
                'longitude' => 107.6304200,
                'controller_device_id' => 'CTRL-MESH-001',
                'is_active' => true,
                'power_status' => 'ON',
                'operation_mode' => 'ADAPTIVE',
                'intensity_percent' => 100,
                'connectivity_status' => 'ONLINE',
                'lux_value' => 14.50,
                'activity_detected' => true,
                'voltage_volt' => 221.40,
                'current_ampere' => 0.452,
                'power_watt' => 100.07,
                'profile_name' => 'Profil Area Gerbang Utama',
                'lux_threshold' => 25.00,
                'hysteresis_lux' => 15.00,
                'dim_intensity_percent' => 40,
                'bright_intensity_percent' => 100,
            ],
            [
                'lamp_code' => 'PJU-TELU-002',
                'name' => 'Tiang PJU Gedung Tokong Nanas (GKU)',
                'location' => 'Koridor Utama Gedung Kuliah Umum, Bandung',
                'latitude' => -6.9726800,
                'longitude' => 107.6312500,
                'controller_device_id' => 'CTRL-MESH-002',
                'is_active' => true,
                'power_status' => 'ON',
                'operation_mode' => 'ADAPTIVE',
                'intensity_percent' => 30,
                'connectivity_status' => 'ONLINE',
                'lux_value' => 11.20,
                'activity_detected' => false,
                'voltage_volt' => 220.10,
                'current_ampere' => 0.136,
                'power_watt' => 29.93,
                'profile_name' => 'Profil Hemat Koridor Kampus',
                'lux_threshold' => 25.00,
                'hysteresis_lux' => 15.00,
                'dim_intensity_percent' => 30,
                'bright_intensity_percent' => 100,
            ],
            [
                'lamp_code' => 'PJU-TELU-003',
                'name' => 'Tiang PJU Danau Galau & Taman Kampus',
                'location' => 'Area Danau & Jogging Track Telkom University',
                'latitude' => -6.9739500,
                'longitude' => 107.6319000,
                'controller_device_id' => 'CTRL-MESH-003',
                'is_active' => true,
                'power_status' => 'OFF',
                'operation_mode' => 'ADAPTIVE',
                'intensity_percent' => 0,
                'connectivity_status' => 'ONLINE',
                'lux_value' => 68.00,
                'activity_detected' => false,
                'voltage_volt' => 222.00,
                'current_ampere' => 0.000,
                'power_watt' => 0.00,
                'profile_name' => 'Profil Taman & Danau',
                'lux_threshold' => 30.00,
                'hysteresis_lux' => 15.00,
                'dim_intensity_percent' => 25,
                'bright_intensity_percent' => 100,
            ],
            [
                'lamp_code' => 'PJU-TELU-004',
                'name' => 'Tiang PJU Asrama Putra & Putri',
                'location' => 'Jl. Jalaprang - Kawasan Asrama Mahasiswa',
                'latitude' => -6.9718000,
                'longitude' => 107.6295000,
                'controller_device_id' => 'CTRL-MESH-004',
                'is_active' => true,
                'power_status' => 'ON',
                'operation_mode' => 'ADAPTIVE',
                'intensity_percent' => 100,
                'connectivity_status' => 'ONLINE',
                'lux_value' => 9.80,
                'activity_detected' => true,
                'voltage_volt' => 219.80,
                'current_ampere' => 0.445,
                'power_watt' => 97.81,
                'profile_name' => 'Profil Zona Asrama',
                'lux_threshold' => 25.00,
                'hysteresis_lux' => 10.00,
                'dim_intensity_percent' => 35,
                'bright_intensity_percent' => 100,
            ],
        ];

        foreach ($lampDefinitions as $item) {
            $lamp = Lamp::updateOrCreate(
                ['lamp_code' => $item['lamp_code']],
                [
                    'name' => $item['name'],
                    'location' => $item['location'],
                    'latitude' => $item['latitude'],
                    'longitude' => $item['longitude'],
                    'controller_device_id' => $item['controller_device_id'],
                    'is_active' => $item['is_active'],
                    'power_status' => $item['power_status'],
                    'operation_mode' => $item['operation_mode'],
                    'intensity_percent' => $item['intensity_percent'],
                    'connectivity_status' => $item['connectivity_status'],
                    'lux_value' => $item['lux_value'],
                    'activity_detected' => $item['activity_detected'],
                    'voltage_volt' => $item['voltage_volt'],
                    'current_ampere' => $item['current_ampere'],
                    'power_watt' => $item['power_watt'],
                    'last_seen_at' => now(),
                ],
            );

            OperationParameter::updateOrCreate(
                ['lamp_id' => $lamp->id],
                [
                    'profile_name' => $item['profile_name'],
                    'lux_threshold' => $item['lux_threshold'],
                    'hysteresis_lux' => $item['hysteresis_lux'],
                    'dim_intensity_percent' => $item['dim_intensity_percent'],
                    'bright_intensity_percent' => $item['bright_intensity_percent'],
                    'energy_saving_start_time' => '22:00',
                    'energy_saving_end_time' => '05:00',
                    'is_custom' => true,
                    'is_active' => true,
                    'synced_to_gateway_at' => now(),
                ],
            );

            SensorReading::create([
                'lamp_id' => $lamp->id,
                'gateway_id' => 'GW-TELU-01',
                'lux_value' => $item['lux_value'],
                'activity_detected' => $item['activity_detected'],
                'voltage_volt' => $item['voltage_volt'],
                'current_ampere' => $item['current_ampere'],
                'power_watt' => $item['power_watt'],
                'power_status' => $item['power_status'],
                'intensity_percent' => $item['intensity_percent'],
                'recorded_at' => now(),
            ]);
        }
    }
}
