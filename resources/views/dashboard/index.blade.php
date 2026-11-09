@extends('layouts.app', ['title' => 'Dashboard Monitoring Smart-PJU'])

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header & Real-time Status Banner -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Dashboard Monitoring & Kendali Smart-PJU</h1>
            <p class="text-sm text-slate-400">
                Pemantauan kondisi lampu real-time, telemetri sensor Gateway, dan manajemen parameter operasi adaptif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <button
                type="button"
                id="btn-refresh-monitoring"
                onclick="refreshRealtimeMonitoring()"
                class="inline-flex items-center gap-2 rounded-xl border border-emerald-500/40 bg-emerald-500/15 px-3.5 py-2 text-xs font-semibold text-emerald-300 hover:bg-emerald-500/25 transition cursor-pointer"
            >
                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Sinkronisasi Real-Time</span>
            </button>
            @if (auth()->user()->isPengelola())
                <button
                    type="button"
                    id="btn-open-add-lamp"
                    onclick="document.getElementById('modal-add-lamp').classList.remove('hidden')"
                    class="rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-slate-950 hover:bg-amber-400 transition cursor-pointer"
                >
                    + Tambah Data Lampu (KAN-34)
                </button>
            @endif
        </div>
    </div>

    <!-- Real-Time KPI Summary Cards (KAN-39) -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Perangkat Lampu</span>
                <span class="rounded-lg bg-slate-800 px-2 py-1 text-xs font-mono text-slate-300">KAN-35</span>
            </div>
            <div id="stat-total-lamps" class="mt-2 text-3xl font-bold text-white">{{ $stats['total_lamps'] }}</div>
            <div class="mt-1 text-xs text-slate-400">
                <span id="stat-active-lamps" class="font-semibold text-emerald-400">{{ $stats['active_lamps'] }}</span> aktif beroperasi
            </div>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status Menyala (ON)</span>
                <span class="rounded-lg bg-emerald-500/15 px-2 py-1 text-xs font-mono text-emerald-300">KAN-44</span>
            </div>
            <div id="stat-lamps-on" class="mt-2 text-3xl font-bold text-emerald-400">{{ $stats['lamps_on'] }}</div>
            <div class="mt-1 text-xs text-slate-400">Otomatis via ambang cahaya (Histeresis)</div>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Mode Redup Adaptif</span>
                <span class="rounded-lg bg-amber-500/15 px-2 py-1 text-xs font-mono text-amber-300">KAN-45</span>
            </div>
            <div id="stat-lamps-dimmed" class="mt-2 text-3xl font-bold text-amber-400">{{ $stats['lamps_dimmed'] }}</div>
            <div class="mt-1 text-xs text-slate-400">Hemat energi saat minim aktivitas</div>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Beban Daya Saat Ini</span>
                <span class="rounded-lg bg-sky-500/15 px-2 py-1 text-xs font-mono text-sky-300">KAN-39</span>
            </div>
            <div class="mt-2 text-3xl font-bold text-sky-400">
                <span id="stat-total-watt">{{ number_format($stats['total_power_watt'], 1) }}</span> <span class="text-lg font-normal">W</span>
            </div>
            <div class="mt-1 text-xs text-slate-400">
                <span class="font-semibold text-sky-300">{{ $stats['lamps_online'] }}</span> node terhubung ke Gateway
            </div>
        </div>
    </div>

    <!-- GIS Map & Live Telemetry Simulator (KAN-39, KAN-38, KAN-44, KAN-45) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Interactive GIS Map (KAN-39) -->
        <div class="lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/80 p-5 flex flex-col gap-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-white">Peta Sebaran & Kondisi Lampu Real-Time (GIS)</h2>
                    <p class="text-xs text-slate-400">Klik titik tiang lampu untuk melihat pembacaan sensor cahaya, arus, dan intensitas terkini (KAN-39)</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center gap-1.5 text-emerald-300"><span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span> ON (100%)</span>
                    <span class="inline-flex items-center gap-1.5 text-amber-300"><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span> Redup Adaptif</span>
                    <span class="inline-flex items-center gap-1.5 text-slate-400"><span class="h-2.5 w-2.5 rounded-full bg-slate-500"></span> OFF / Nonaktif</span>
                </div>
            </div>
            <div id="pju-gis-map" class="h-80 w-full rounded-xl border border-slate-800 bg-slate-950 z-10"></div>
        </div>

        <!-- Gateway Telemetry & Adaptive Controller Panel (KAN-38, KAN-44, KAN-45) -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 flex flex-col gap-4">
            <div>
                <span class="inline-block rounded-md bg-sky-500/15 px-2 py-0.5 text-[11px] font-mono font-semibold text-sky-300 mb-1">
                    IoT Gateway & Controller Node
                </span>
                <h2 class="text-base font-bold text-white">Ingest Telemetri Sensor & Kendali Adaptif</h2>
                <p class="text-xs text-slate-400">Kirim pembacaan sensor cahaya, aktivitas PIR, dan kelistrikan melalui Gateway (KAN-38, KAN-44, KAN-45)</p>
            </div>

            <form method="POST" action="{{ route('telemetry.simulate') }}" class="flex flex-col gap-3">
                @csrf
                <div>
                    <label for="sim_lamp_id" class="block text-xs font-semibold text-slate-300 mb-1">Pilih Perangkat Lampu / Controller</label>
                    <select id="sim_lamp_id" name="lamp_id" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-500 focus:outline-none">
                        @foreach ($allLamps as $lampItem)
                            <option value="{{ $lampItem->id }}">
                                {{ $lampItem->lamp_code }} — {{ $lampItem->controller_device_id }} ({{ $lampItem->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="sim_lux_value" class="block text-xs font-semibold text-slate-300 mb-1">Sensor Cahaya (Lux)</label>
                        <input id="sim_lux_value" type="number" step="0.1" name="lux_value" value="12.0" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label for="sim_activity" class="block text-xs font-semibold text-slate-300 mb-1">Deteksi Aktivitas (PIR)</label>
                        <select id="sim_activity" name="activity_detected" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                            <option value="1">Ada Aktivitas (Terang 100%)</option>
                            <option value="0">Sepi / Tanpa Aktivitas (Redup)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="sim_voltage" class="block text-xs font-semibold text-slate-300 mb-1">Tegangan (Volt)</label>
                        <input id="sim_voltage" type="number" step="0.1" name="voltage_volt" value="220.0" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label for="sim_current" class="block text-xs font-semibold text-slate-300 mb-1">Arus Listrik (Ampere)</label>
                        <input id="sim_current" type="number" step="0.001" name="current_ampere" value="0.450" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                </div>

                <input type="hidden" name="gateway_id" value="GW-TELU-01">

                <button type="submit" id="btn-submit-telemetry" class="mt-1 w-full rounded-xl bg-sky-500 px-4 py-2.5 text-xs font-bold text-slate-950 hover:bg-sky-400 transition cursor-pointer">
                    Kirim Payload Telemetri via Gateway (KAN-38)
                </button>
            </form>
        </div>
    </div>

    <!-- Lamp Master Data Table & Real-Time Sensor Conditions (KAN-34, KAN-35, KAN-36, KAN-37, KAN-39, KAN-44, KAN-45) -->
    <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6 flex flex-col gap-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-white">Manajemen Data Lampu & Monitoring Kondisi Terkini</h2>
                <p class="text-xs text-slate-400">
                    Daftar perangkat lampu terdaftar beserta lokasi, ID perangkat pengendali, sensor cahaya/arus, serta aksi cepat (KAN-34 s/d KAN-39, KAN-44, KAN-45)
                </p>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari kode, nama, lokasi, ID controller..."
                    class="rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none"
                >
                <button type="submit" class="rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2 text-xs font-semibold text-white hover:bg-slate-700">
                    Filter
                </button>
                @if ($search !== '')
                    <a href="{{ route('dashboard') }}" class="text-xs text-amber-400 hover:underline">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-800 bg-slate-950/60 text-xs uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-3.5 py-3">Kode & Perangkat</th>
                        <th class="px-3.5 py-3">Lokasi & Koordinat</th>
                        <th class="px-3.5 py-3">Status & Mode</th>
                        <th class="px-3.5 py-3">Sensor Cahaya & Aktivitas</th>
                        <th class="px-3.5 py-3">Kelistrikan & Update</th>
                        <th class="px-3.5 py-3">Kendali Cepat (KAN-44/45)</th>
                        <th class="px-3.5 py-3 text-right">Manajemen (KAN-35/36/37)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($lamps as $lamp)
                        <tr class="hover:bg-slate-800/35">
                            <td class="px-3.5 py-3.5">
                                <div class="font-mono font-bold text-amber-400">{{ $lamp->lamp_code }}</div>
                                <div class="font-semibold text-white text-xs">{{ $lamp->name }}</div>
                                <div class="mt-0.5 font-mono text-[11px] text-sky-300">ID: {{ $lamp->controller_device_id }}</div>
                            </td>
                            <td class="px-3.5 py-3.5">
                                <div class="text-xs text-slate-200">{{ $lamp->location }}</div>
                                <div class="mt-0.5 font-mono text-[11px] text-slate-400">
                                    {{ number_format($lamp->latitude, 5) }}, {{ number_format($lamp->longitude, 5) }}
                                </div>
                            </td>
                            <td class="px-3.5 py-3.5">
                                @if (! $lamp->is_active)
                                    <span class="inline-flex items-center rounded-full bg-rose-500/20 border border-rose-500/30 px-2.5 py-0.5 text-xs font-semibold text-rose-300">
                                        NONAKTIF
                                    </span>
                                @elseif ($lamp->power_status === 'ON')
                                    <span class="inline-flex items-center rounded-full {{ $lamp->intensity_percent < 100 ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }} px-2.5 py-0.5 text-xs font-semibold">
                                        ON • {{ $lamp->intensity_percent }}%
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-800 border border-slate-700 px-2.5 py-0.5 text-xs font-semibold text-slate-300">
                                        OFF • 0%
                                    </span>
                                @endif
                                <div class="mt-1 text-[11px] text-slate-400">
                                    Mode: <span class="font-semibold text-slate-300">{{ $lamp->operation_mode }}</span>
                                </div>
                            </td>
                            <td class="px-3.5 py-3.5 text-xs">
                                <div class="text-slate-200">Cahaya: <span class="font-bold text-white">{{ $lamp->lux_value }} Lux</span></div>
                                <div class="mt-0.5 text-slate-400">
                                    Aktivitas:
                                    <span class="{{ $lamp->activity_detected ? 'text-emerald-300 font-semibold' : 'text-amber-300' }}">
                                        {{ $lamp->activity_detected ? 'Terdeteksi (100%)' : 'Sepi (Redup)' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-3.5 py-3.5 text-xs">
                                <div class="text-slate-200">{{ $lamp->voltage_volt }} V • {{ $lamp->current_ampere }} A</div>
                                <div class="font-semibold text-amber-300">{{ $lamp->power_watt }} W</div>
                                <div class="text-[11px] text-slate-500">
                                    Update: {{ $lamp->last_seen_at ? $lamp->last_seen_at->diffForHumans() : 'Belum ada' }}
                                </div>
                            </td>
                            <td class="px-3.5 py-3.5">
                                <div class="flex flex-wrap gap-1.5">
                                    <!-- KAN-44 Quick Hysteresis Lux Toggle -->
                                    <form method="POST" action="{{ route('lamps.power-status', $lamp) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="lux_value" value="{{ $lamp->power_status === 'ON' ? '75' : '10' }}">
                                        <button type="submit" class="rounded-lg border border-slate-700 bg-slate-800/90 px-2.5 py-1 text-[11px] font-semibold text-slate-200 hover:border-amber-500/40 hover:text-amber-300 cursor-pointer" title="Simulasi perubahan ambang cahaya KAN-44">
                                            {{ $lamp->power_status === 'ON' ? 'Set Terang (OFF)' : 'Set Gelap (ON)' }}
                                        </button>
                                    </form>

                                    <!-- KAN-45 Quick Adaptive Dimming Toggle -->
                                    <form method="POST" action="{{ route('lamps.intensity', $lamp) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="activity_detected" value="{{ $lamp->activity_detected ? '0' : '1' }}">
                                        <button type="submit" class="rounded-lg border border-slate-700 bg-slate-800/90 px-2.5 py-1 text-[11px] font-semibold text-sky-300 hover:border-sky-500/40 cursor-pointer" title="Simulasi deteksi aktivitas adaptif KAN-45">
                                            {{ $lamp->activity_detected ? 'Redupkan (Sepi)' : 'Terangkan (Aktif)' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td class="px-3.5 py-3.5 text-right">
                                <div class="inline-flex flex-wrap justify-end gap-1.5">
                                    <a href="{{ route('lamps.show', $lamp) }}" class="rounded-lg border border-slate-700 bg-slate-800 px-2.5 py-1 text-xs font-semibold text-slate-200 hover:bg-slate-700">
                                        Detail
                                    </a>
                                    @if (auth()->user()->isPengelola())
                                        <button
                                            type="button"
                                            onclick="openEditLampModal({{ Js::from($lamp) }})"
                                            class="rounded-lg border border-amber-500/40 bg-amber-500/15 px-2.5 py-1 text-xs font-semibold text-amber-300 hover:bg-amber-500/25 cursor-pointer"
                                        >
                                            Edit
                                        </button>
                                        @if ($lamp->is_active)
                                            <form method="POST" action="{{ route('lamps.deactivate', $lamp) }}" onsubmit="return confirm('Nonaktifkan perangkat lampu {{ $lamp->lamp_code }}?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg border border-rose-500/40 bg-rose-500/15 px-2.5 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-500/25 cursor-pointer">
                                                    Nonaktifkan
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('lamps.destroy', $lamp) }}" onsubmit="return confirm('Hapus permanen data lampu {{ $lamp->lamp_code }} dari sistem?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-rose-600/40 bg-rose-950/60 px-2.5 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-900/60 cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada perangkat lampu terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Operation Parameter Profiles Section (KAN-40, KAN-41, KAN-42, KAN-43) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Active Operation Parameters List & Details (KAN-41, KAN-42, KAN-43) -->
        <div class="lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/80 p-6 flex flex-col gap-4">
            <div>
                <h2 class="text-lg font-bold text-white">Profil Parameter Operasi Lampu Aktif (KAN-41, KAN-42, KAN-43)</h2>
                <p class="text-xs text-slate-400">
                    Konfigurasi ambang batas cahaya (Lux), margin histeresis, setelan intensitas redup/terang, serta periode hemat energi
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-800 bg-slate-950/60 text-xs uppercase text-slate-400">
                        <tr>
                            <th class="px-3.5 py-3">Lampu & Nama Profil</th>
                            <th class="px-3.5 py-3">Ambang Cahaya (Lux)</th>
                            <th class="px-3.5 py-3">Redup / Terang</th>
                            <th class="px-3.5 py-3">Periode Hemat</th>
                            <th class="px-3.5 py-3">Sinkronisasi Gateway</th>
                            @if (auth()->user()->isPengelola())
                                <th class="px-3.5 py-3 text-right">Aksi (KAN-42/43)</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($operationParameters as $param)
                            <tr class="hover:bg-slate-800/35">
                                <td class="px-3.5 py-3">
                                    <div class="font-mono text-xs font-bold text-amber-400">
                                        {{ $param->lamp?->lamp_code ?? 'GLOBAL' }}
                                    </div>
                                    <div class="font-semibold text-white text-xs">{{ $param->profile_name }}</div>
                                    <span class="inline-block mt-0.5 rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $param->is_custom ? 'bg-amber-500/20 text-amber-300' : 'bg-slate-800 text-slate-300' }}">
                                        {{ $param->is_custom ? 'Profil Kustom' : 'Default Aman' }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-3 text-xs text-slate-200">
                                    <div>Nyala: <span class="font-bold text-white">&lt; {{ $param->lux_threshold }} lx</span></div>
                                    <div class="text-slate-400">Mati: &gt; {{ $param->lux_threshold + $param->hysteresis_lux }} lx (Histeresis {{ $param->hysteresis_lux }} lx)</div>
                                </td>
                                <td class="px-3.5 py-3 text-xs">
                                    <span class="font-semibold text-amber-300">{{ $param->dim_intensity_percent }}%</span>
                                    <span class="text-slate-500">/</span>
                                    <span class="font-semibold text-emerald-300">{{ $param->bright_intensity_percent }}%</span>
                                </td>
                                <td class="px-3.5 py-3 font-mono text-xs text-slate-200">
                                    {{ $param->energy_saving_start_time }} - {{ $param->energy_saving_end_time }}
                                </td>
                                <td class="px-3.5 py-3 text-xs text-slate-400">
                                    {{ $param->synced_to_gateway_at ? $param->synced_to_gateway_at->format('H:i:s d/m/Y') : 'Belum sinkron' }}
                                </td>
                                @if (auth()->user()->isPengelola())
                                    <td class="px-3.5 py-3 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button
                                                type="button"
                                                onclick="openEditParamModal({{ Js::from($param) }})"
                                                class="rounded-lg border border-amber-500/40 bg-amber-500/15 px-2.5 py-1 text-xs font-semibold text-amber-300 hover:bg-amber-500/25 cursor-pointer"
                                            >
                                                Ubah (KAN-42)
                                            </button>
                                            <form method="POST" action="{{ route('operation-parameters.destroy', $param) }}" onsubmit="return confirm('Reset profil parameter operasi ini ke nilai bawaan yang aman (KAN-43)?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-rose-500/40 bg-rose-500/15 px-2.5 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-500/25 cursor-pointer">
                                                    Reset Default
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-slate-400">Belum ada profil parameter operasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Operation Parameter Profile Form (KAN-40) -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6 flex flex-col gap-4">
            <div>
                <span class="inline-block rounded-md bg-amber-500/15 px-2 py-0.5 text-[11px] font-mono font-semibold text-amber-300 mb-1">
                    KAN-40 • Konfigurasi Operasi
                </span>
                <h2 class="text-base font-bold text-white">Tambah / Setel Profil Parameter Operasi</h2>
                <p class="text-xs text-slate-400">Atur ambang cahaya, intensitas redup/terang, dan jam hemat energi untuk lampu</p>
            </div>

            @if (auth()->user()->isPengelola())
                <form method="POST" action="{{ route('operation-parameters.store') }}" class="flex flex-col gap-3">
                    @csrf
                    <div>
                        <label for="param_lamp_id" class="block text-xs font-semibold text-slate-300 mb-1">Target Lampu PJU</label>
                        <select id="param_lamp_id" name="lamp_id" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                            @foreach ($allLamps as $lampItem)
                                <option value="{{ $lampItem->id }}">{{ $lampItem->lamp_code }} — {{ $lampItem->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="param_profile_name" class="block text-xs font-semibold text-slate-300 mb-1">Nama Profil Operasi</label>
                        <input id="param_profile_name" type="text" name="profile_name" required placeholder="Contoh: Profil Malam Kampus" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="param_lux_threshold" class="block text-xs font-semibold text-slate-300 mb-1">Ambang Gelap (Lux)</label>
                            <input id="param_lux_threshold" type="number" step="0.1" name="lux_threshold" value="25" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                        </div>
                        <div>
                            <label for="param_hysteresis" class="block text-xs font-semibold text-slate-300 mb-1">Histeresis (+Lux)</label>
                            <input id="param_hysteresis" type="number" step="0.1" name="hysteresis_lux" value="15" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="param_dim" class="block text-xs font-semibold text-slate-300 mb-1">Setelan Redup (%)</label>
                            <input id="param_dim" type="number" name="dim_intensity_percent" value="30" min="0" max="100" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                        </div>
                        <div>
                            <label for="param_bright" class="block text-xs font-semibold text-slate-300 mb-1">Setelan Terang (%)</label>
                            <input id="param_bright" type="number" name="bright_intensity_percent" value="100" min="1" max="100" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="param_start" class="block text-xs font-semibold text-slate-300 mb-1">Mulai Hemat Energi</label>
                            <input id="param_start" type="time" name="energy_saving_start_time" value="22:00" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                        </div>
                        <div>
                            <label for="param_end" class="block text-xs font-semibold text-slate-300 mb-1">Akhir Hemat Energi</label>
                            <input id="param_end" type="time" name="energy_saving_end_time" value="05:00" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                        </div>
                    </div>

                    <button type="submit" id="btn-save-parameter" class="mt-1 w-full rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-bold text-slate-950 hover:bg-amber-400 transition cursor-pointer">
                        Simpan Profil Parameter Operasi (KAN-40)
                    </button>
                </form>
            @else
                <div class="rounded-xl border border-slate-800 bg-slate-950 p-4 text-xs text-slate-400">
                    Anda masuk sebagai <strong>Teknisi</strong>. Penambahan & perubahan parameter operasi dikhususkan bagi <strong>Pengelola</strong>.
                </div>
            @endif
        </div>
    </div>
</div>

@if (auth()->user()->isPengelola())
    <!-- Modal Tambah Lampu Baru (KAN-34) -->
    <div id="modal-add-lamp" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-lg font-bold text-white">Tambah Data Lampu PJU Baru (KAN-34)</h3>
                <button type="button" onclick="document.getElementById('modal-add-lamp').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('lamps.store') }}" class="mt-4 flex flex-col gap-3.5">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kode Lampu</label>
                        <input type="text" name="lamp_code" required placeholder="PJU-TELU-005" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">ID Perangkat Pengendali</label>
                        <input type="text" name="controller_device_id" required placeholder="CTRL-MESH-005" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Tiang / Titik Lampu</label>
                    <input type="text" name="name" required placeholder="Tiang PJU Fakultas Informatika" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Lokasi Lengkap</label>
                    <input type="text" name="location" required placeholder="Jl. Telekomunikasi Gedung Panambulai, Bandung" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Latitude</label>
                        <input type="number" step="0.0000001" name="latitude" value="-6.9732000" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Longitude</label>
                        <input type="number" step="0.0000001" name="longitude" value="107.6305000" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                </div>

                <div class="mt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-add-lamp').classList.add('hidden')" class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-300">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-slate-950 hover:bg-amber-400 cursor-pointer">
                        Simpan Data Lampu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Data Lampu (KAN-36) -->
    <div id="modal-edit-lamp" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-lg font-bold text-white">Ubah Data Lampu Terdaftar (KAN-36)</h3>
                <button type="button" onclick="document.getElementById('modal-edit-lamp').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form id="form-edit-lamp" method="POST" action="" class="mt-4 flex flex-col gap-3.5">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kode Lampu</label>
                        <input id="edit_lamp_code" type="text" name="lamp_code" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">ID Perangkat Pengendali</label>
                        <input id="edit_controller_id" type="text" name="controller_device_id" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Tiang / Titik Lampu</label>
                    <input id="edit_name" type="text" name="name" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Lokasi Lengkap</label>
                    <input id="edit_location" type="text" name="location" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Latitude</label>
                        <input id="edit_latitude" type="number" step="0.0000001" name="latitude" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Longitude</label>
                        <input id="edit_longitude" type="number" step="0.0000001" name="longitude" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                </div>

                <div>
                    <label class="inline-flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                        <input id="edit_is_active" type="checkbox" name="is_active" value="1" class="rounded border-slate-700 bg-slate-950 text-amber-500">
                        <span>Perangkat Lampu Aktif Beroperasi</span>
                    </label>
                </div>

                <div class="mt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-edit-lamp').classList.add('hidden')" class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-300">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-slate-950 hover:bg-amber-400 cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Parameter Operasi (KAN-42) -->
    <div id="modal-edit-param" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-lg font-bold text-white">Ubah Parameter Operasi & Sinkronkan Gateway (KAN-42)</h3>
                <button type="button" onclick="document.getElementById('modal-edit-param').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form id="form-edit-param" method="POST" action="" class="mt-4 flex flex-col gap-3.5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Profil Operasi</label>
                    <input id="edit_param_profile_name" type="text" name="profile_name" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Ambang Cahaya Gelap (Lux)</label>
                        <input id="edit_param_lux" type="number" step="0.1" name="lux_threshold" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Histeresis (+Lux)</label>
                        <input id="edit_param_hysteresis" type="number" step="0.1" name="hysteresis_lux" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Intensitas Redup (%)</label>
                        <input id="edit_param_dim" type="number" min="0" max="100" name="dim_intensity_percent" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Intensitas Terang (%)</label>
                        <input id="edit_param_bright" type="number" min="1" max="100" name="bright_intensity_percent" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Mulai Hemat Energi</label>
                        <input id="edit_param_start" type="time" name="energy_saving_start_time" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Akhir Hemat Energi</label>
                        <input id="edit_param_end" type="time" name="energy_saving_end_time" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white">
                    </div>
                </div>

                <div class="mt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-edit-param').classList.add('hidden')" class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-300">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-slate-950 hover:bg-amber-400 cursor-pointer">
                        Perbarui & Sinkronkan ke Gateway
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

@push('scripts')
<script>
    const initialLamps = {{ Js::from($allLamps) }};
    let mapInstance = null;
    let markerLayerGroup = null;

    function initSmartPjuMap() {
        const mapContainer = document.getElementById('pju-gis-map');
        if (!mapContainer || typeof L === 'undefined') {
            return;
        }

        mapInstance = L.map('pju-gis-map').setView([-6.9730, 107.6308], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap Smart-PJU'
        }).addTo(mapInstance);

        markerLayerGroup = L.layerGroup().addTo(mapInstance);
        renderLampMarkers(initialLamps);
    }

    function renderLampMarkers(lamps) {
        if (!markerLayerGroup) {
            return;
        }
        markerLayerGroup.clearLayers();

        lamps.forEach((lamp) => {
            let color = '#64748b';
            if (lamp.is_active && lamp.power_status === 'ON') {
                color = lamp.intensity_percent < 100 ? '#f59e0b' : '#10b981';
            }

            const marker = L.circleMarker([lamp.latitude, lamp.longitude], {
                radius: 9,
                fillColor: color,
                color: '#0f172a',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.9
            });

            marker.bindPopup(`
                <div style="min-width:190px;font-family:sans-serif;">
                    <div style="font-weight:700;font-size:13px;">${lamp.lamp_code} — ${lamp.name}</div>
                    <div style="font-size:11px;color:#475569;margin-bottom:6px;">${lamp.location}</div>
                    <div style="font-size:12px;"><strong>Controller:</strong> ${lamp.controller_device_id}</div>
                    <div style="font-size:12px;"><strong>Status:</strong> ${lamp.power_status} (${lamp.intensity_percent}%)</div>
                    <div style="font-size:12px;"><strong>Sensor Cahaya:</strong> ${lamp.lux_value} Lux</div>
                    <div style="font-size:12px;"><strong>Daya:</strong> ${lamp.power_watt} W (${lamp.current_ampere} A)</div>
                </div>
            `);

            marker.addTo(markerLayerGroup);
        });
    }

    async function refreshRealtimeMonitoring() {
        try {
            const response = await fetch('{{ route("monitoring.status") }}', {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) {
                return;
            }
            const payload = await response.json();
            if (payload.meta) {
                document.getElementById('stat-total-lamps').textContent = payload.meta.total_lamps;
                document.getElementById('stat-active-lamps').textContent = payload.meta.active_lamps;
                document.getElementById('stat-lamps-on').textContent = payload.meta.lamps_on;
                document.getElementById('stat-total-watt').textContent = Number(payload.meta.total_power_watt).toFixed(1);
            }
            if (Array.isArray(payload.data)) {
                renderLampMarkers(payload.data);
            }
        } catch (error) {
            console.error('Failed to refresh monitoring data:', error);
        }
    }

    function openEditLampModal(lamp) {
        const form = document.getElementById('form-edit-lamp');
        form.action = `/lamps/${lamp.id}`;
        document.getElementById('edit_lamp_code').value = lamp.lamp_code;
        document.getElementById('edit_controller_id').value = lamp.controller_device_id;
        document.getElementById('edit_name').value = lamp.name;
        document.getElementById('edit_location').value = lamp.location;
        document.getElementById('edit_latitude').value = lamp.latitude;
        document.getElementById('edit_longitude').value = lamp.longitude;
        document.getElementById('edit_is_active').checked = Boolean(lamp.is_active);
        document.getElementById('modal-edit-lamp').classList.remove('hidden');
    }

    function openEditParamModal(param) {
        const form = document.getElementById('form-edit-param');
        form.action = `/operation-parameters/${param.id}`;
        document.getElementById('edit_param_profile_name').value = param.profile_name;
        document.getElementById('edit_param_lux').value = param.lux_threshold;
        document.getElementById('edit_param_hysteresis').value = param.hysteresis_lux;
        document.getElementById('edit_param_dim').value = param.dim_intensity_percent;
        document.getElementById('edit_param_bright').value = param.bright_intensity_percent;
        document.getElementById('edit_param_start').value = param.energy_saving_start_time;
        document.getElementById('edit_param_end').value = param.energy_saving_end_time;
        document.getElementById('modal-edit-param').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', initSmartPjuMap);
</script>
@endpush
@endsection

