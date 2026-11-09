@extends('layouts.app', ['title' => 'Daftar Data Lampu — Smart-PJU'])

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Daftar & Detail Data Lampu PJU</h1>
            <p class="text-sm text-slate-400">Informasi kode lampu, lokasi GIS, serta identitas perangkat pengendali terdaftar (KAN-35)</p>
        </div>

        <form method="GET" action="{{ route('lamps.index') }}" class="flex items-center gap-2">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Cari kode, lokasi, ID controller..."
                class="rounded-xl border border-slate-700 bg-slate-900 px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none"
            >
            <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-bold text-slate-950 hover:bg-amber-400 transition">
                Cari
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-800 bg-slate-950/60 text-xs uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5">Kode Lampu</th>
                        <th class="px-4 py-3.5">Nama & Lokasi</th>
                        <th class="px-4 py-3.5">ID Perangkat Pengendali</th>
                        <th class="px-4 py-3.5">Koordinat GIS</th>
                        <th class="px-4 py-3.5">Status & Intensitas</th>
                        <th class="px-4 py-3.5">Sensor Terkini</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($lamps as $lamp)
                        <tr class="hover:bg-slate-800/40">
                            <td class="px-4 py-3.5 font-mono font-bold text-amber-400">{{ $lamp->lamp_code }}</td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-white">{{ $lamp->name }}</div>
                                <div class="text-xs text-slate-400">{{ $lamp->location }}</div>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-xs text-sky-300">{{ $lamp->controller_device_id }}</td>
                            <td class="px-4 py-3.5 text-xs text-slate-400">
                                {{ number_format($lamp->latitude, 5) }}, {{ number_format($lamp->longitude, 5) }}
                            </td>
                            <td class="px-4 py-3.5">
                                @if (! $lamp->is_active)
                                    <span class="inline-flex rounded-full bg-rose-500/20 px-2.5 py-0.5 text-xs font-semibold text-rose-300">NONAKTIF</span>
                                @elseif ($lamp->power_status === 'ON')
                                    <span class="inline-flex rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-xs font-semibold text-emerald-300">
                                        ON ({{ $lamp->intensity_percent }}%)
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-700 px-2.5 py-0.5 text-xs font-semibold text-slate-300">OFF (0%)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-300">
                                <div>Lux: <span class="font-semibold text-white">{{ $lamp->lux_value }} lx</span></div>
                                <div>Daya: <span class="font-semibold text-amber-300">{{ $lamp->power_watt }} W</span> ({{ $lamp->current_ampere }} A)</div>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <a href="{{ route('lamps.show', $lamp) }}" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-semibold text-amber-300 hover:bg-slate-700">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">Tidak ada data lampu yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

