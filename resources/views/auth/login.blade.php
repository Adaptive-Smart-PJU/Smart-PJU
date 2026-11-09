@extends('layouts.app', ['title' => 'Login — Smart-PJU'])

@section('content')
<div class="flex min-h-[80vh] items-center justify-center">
    <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900/90 p-8 shadow-2xl">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl border border-amber-500/40 bg-amber-500/15 text-amber-400 font-bold text-lg">
                PJU
            </div>
            <h1 class="text-2xl font-bold text-white">Masuk ke Smart-PJU</h1>
            <p class="mt-1 text-sm text-slate-400">Sistem Monitoring & Pengendalian Lampu Jalan Pintar Adaptif</p>
        </div>

        <form method="POST" action="{{ route('login.submit') }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Alamat Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    placeholder="pengelola@smartpju.id"
                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none"
                >
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Kata Sandi</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    placeholder="••••••••"
                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none"
                >
            </div>

            <div class="flex items-center justify-between text-xs text-slate-400">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-700 bg-slate-950 text-amber-500">
                    <span>Ingat sesi saya</span>
                </label>
                <a href="{{ route('register') }}" class="font-semibold text-amber-400 hover:text-amber-300">Daftar akun baru</a>
            </div>

            <button
                type="submit"
                id="btn-login"
                class="mt-2 w-full rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 hover:bg-amber-400 transition cursor-pointer"
            >
                Masuk ke Dashboard
            </button>
        </form>

        <div class="mt-6 rounded-xl border border-slate-800 bg-slate-950/70 p-3.5 text-xs text-slate-400">
            <div class="font-semibold text-slate-300 mb-1">Akun Demo Bawaan (Seeder):</div>
            <div>• Pengelola: <code class="text-amber-300">pengelola@smartpju.id</code> / <code class="text-amber-300">password</code></div>
            <div>• Teknisi: <code class="text-sky-300">teknisi@smartpju.id</code> / <code class="text-sky-300">password</code></div>
        </div>
    </div>
</div>
@endsection

