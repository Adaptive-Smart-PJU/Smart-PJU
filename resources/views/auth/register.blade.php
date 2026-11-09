@extends('layouts.app', ['title' => 'Registrasi Akun — Smart-PJU'])

@section('content')
<div class="flex min-h-[80vh] items-center justify-center">
    <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900/90 p-8 shadow-2xl">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl border border-amber-500/40 bg-amber-500/15 text-amber-400 font-bold text-lg">
                PJU
            </div>
            <h1 class="text-2xl font-bold text-white">Registrasi Pengguna</h1>
            <p class="mt-1 text-sm text-slate-400">Buat akun Pengelola atau Teknisi Smart-PJU</p>
        </div>

        <form method="POST" action="{{ route('register.submit') }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Nama Lengkap</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    placeholder="Nama Lengkap Anda"
                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none"
                >
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Alamat Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    placeholder="nama@smartpju.id"
                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none"
                >
            </div>

            <div>
                <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Peran (Role)</label>
                <select
                    id="role"
                    name="role"
                    required
                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-amber-500 focus:outline-none"
                >
                    <option value="pengelola" {{ old('role', 'pengelola') === 'pengelola' ? 'selected' : '' }}>Pengelola (Admin / Operator Lampu)</option>
                    <option value="teknisi" {{ old('role') === 'teknisi' ? 'selected' : '' }}>Teknisi Lapangan</option>
                </select>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Kata Sandi</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    placeholder="Minimal 8 karakter"
                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none"
                >
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Konfirmasi Kata Sandi</label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    placeholder="Ulangi kata sandi"
                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none"
                >
            </div>

            <button
                type="submit"
                id="btn-register"
                class="mt-2 w-full rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 hover:bg-amber-400 transition cursor-pointer"
            >
                Daftar & Masuk
            </button>
        </form>

        <p class="mt-5 text-center text-xs text-slate-400">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="font-semibold text-amber-400 hover:text-amber-300">Masuk di sini</a>
        </p>
    </div>
</div>
@endsection

