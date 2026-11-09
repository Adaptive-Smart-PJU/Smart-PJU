@extends('layouts.app', ['title' => "Detail Lampu {$lamp->lamp_code} — Smart-PJU"])

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('lamps.index') }}" class="text-xs font-semibold text-amber-400 hover:text-amber-300">&larr; Kembali ke Daftar Lampu</a>
            <h1 class="mt-1 text-2xl font-bold text-white">{{ $lamp->name }} ({{ $lamp->lamp_code }})</h1>
            <p class="text-sm text-slate-400">{{ $lamp->location }}</p>
        </div>
        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $lamp->is_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30' }}">
            {{ $lamp->is_active ? 'PERANGKAT AKTIF' : 'PERANGKAT NONAKTIF' }}
        </span>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6">
            <h2 class="text-base font-bold text-white mb-4">Identitas & Lokasi Perangkat (KAN-35)</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs text-slate-400">Kode Lampu</dt>
                    <dd class="font-mono font-bold text-amber-400">{{ $lamp->lamp_code }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">ID Perangkat Pengendali</dt>
                    <dd class="font-mono font-bold text-sky-300">{{ $lamp->controller_device_id }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Koordinat Latitude</dt>
                    <dd class="text-white">{{ $lamp->latitude }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Koordinat Longitude</dt>
                    <dd class="text-white">{{ $lamp->longitude }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Status Nyala & Intensitas</dt>
                    <dd class="font-semibold text-white">{{ $lamp->power_status }} ({{ $lamp->intensity_percent }}%)</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Sensor Cahaya & Listrik</dt>
                    <dd class="text-white">{{ $lamp->lux_value }} Lux | {{ $lamp->voltage_volt }} V | {{ $lamp->current_ampere }} A ({{ $lamp->power_watt }} W)</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6">
            <h2 class="text-base font-bold text-white mb-4">Profil Parameter Operasi Aktif (KAN-41)</h2>
            @if ($lamp->operationParameter)
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div class="col-span-2">
                        <dt class="text-xs text-slate-400">Nama Profil</dt>
                        <dd class="font-semibold text-amber-300">{{ $lamp->operationParameter->profile_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Ambang Cahaya (Lux)</dt>
                        <dd class="text-white">{{ $lamp->operationParameter->lux_threshold }} Lux (Histeresis +{{ $lamp->operationParameter->hysteresis_lux }} Lux)</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Intensitas Redup / Terang</dt>
                        <dd class="text-white">{{ $lamp->operationParameter->dim_intensity_percent }}% / {{ $lamp->operationParameter->bright_intensity_percent }}%</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Periode Hemat Energi</dt>
                        <dd class="text-white">{{ $lamp->operationParameter->energy_saving_start_time }} - {{ $lamp->operationParameter->energy_saving_end_time }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Tipe Konfigurasi</dt>
                        <dd class="text-white">{{ $lamp->operationParameter->is_custom ? 'Profil Kustom' : 'Bawaan Aman (Default)' }}</dd>
                    </div>
                </dl>
            @else
                <p class="text-sm text-slate-400">Menggunakan parameter bawaan sistem (Ambang 25 Lux, Redup 30%, Terang 100%).</p>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6">
        <h2 class="text-base font-bold text-white mb-4">Riwayat Telemetri Sensor Terakhir (KAN-38)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-xs uppercase text-slate-400">
                    <tr>
                        <th class="px-3 py-2">Waktu</th>
                        <th class="px-3 py-2">Gateway</th>
                        <th class="px-3 py-2">Cahaya (Lux)</th>
                        <th class="px-3 py-2">Aktivitas</th>
                        <th class="px-3 py-2">Tegangan / Arus / Daya</th>
                        <th class="px-3 py-2">Status & Intensitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($lamp->sensorReadings as $reading)
                        <tr>
                            <td class="px-3 py-2 text-xs text-slate-400">{{ $reading->recorded_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="px-3 py-2 font-mono text-xs text-sky-300">{{ $reading->gateway_id }}</td>
                            <td class="px-3 py-2 text-white">{{ $reading->lux_value }} lx</td>
                            <td class="px-3 py-2 text-xs">{{ $reading->activity_detected ? 'Ada Aktivitas' : 'Tidak Ada' }}</td>
                            <td class="px-3 py-2 text-xs text-slate-300">{{ $reading->voltage_volt }} V / {{ $reading->current_ampere }} A / {{ $reading->power_watt }} W</td>
                            <td class="px-3 py-2 text-xs font-semibold text-amber-300">{{ $reading->power_status }} ({{ $reading->intensity_percent }}%)</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-center text-slate-400">Belum ada pembacaan sensor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

