<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Smart-PJU — Adaptive Smart Street Lighting' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased selection:bg-amber-500 selection:text-slate-950">
    @auth
        <header class="sticky top-0 z-40 border-b border-slate-800 bg-slate-900/90 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3.5 sm:px-6 lg:px-8">
                <div class="flex items-center gap-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/15 border border-amber-500/40 text-amber-400 font-bold">
                            PJU
                        </div>
                        <div>
                            <span class="text-lg font-bold tracking-tight text-white">Smart-PJU</span>
                            <span class="block text-xs text-slate-400">Adaptive Street Lighting IoT</span>
                        </div>
                    </a>
                    <nav class="hidden md:flex items-center gap-2 ml-4">
                        <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-amber-400' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            Dashboard Monitoring
                        </a>
                        <a href="{{ route('lamps.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('lamps.*') ? 'bg-slate-800 text-amber-400' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            Daftar & Detail Lampu
                        </a>
                    </nav>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <div class="text-sm font-semibold text-white">{{ auth()->user()->name }}</div>
                        <div class="flex items-center justify-end gap-1.5">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ auth()->user()->isPengelola() ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-sky-500/20 text-sky-300 border border-sky-500/30' }}">
                                {{ auth()->user()->role->label() }}
                            </span>
                            <span class="text-xs text-slate-400 hidden sm:inline">{{ auth()->user()->email }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" id="btn-logout" class="rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2 text-xs font-semibold text-slate-200 hover:border-rose-500/50 hover:bg-rose-500/15 hover:text-rose-300 transition">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>
    @endauth

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @if (session('status'))
            <div role="alert" class="mb-6 flex items-center justify-between rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mb-6 rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <p class="font-semibold mb-1">Terdapat kesalahan pada input:</p>
                <ul class="list-disc pl-5 flex flex-col gap-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    @stack('scripts')
</body>
</html>

