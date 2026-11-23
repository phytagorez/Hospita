@extends('layouts.app')

@section('head')
    @vite('resources/css/app.css')
    <title>Sesi perlu diperbarui - Hospia</title>
@endsection

@section('content')
    <main class="grid min-h-screen place-items-center bg-slate-50 px-5 py-12 text-slate-800 dark:bg-slate-950 dark:text-slate-100">
        <section class="card w-full max-w-lg">
            <p class="section-kicker">HOSPIA / PERLINDUNGAN SESI</p>
            <h1 class="mt-4 text-2xl font-semibold">Sesi perlu diperbarui</h1>
            <p class="mt-4 text-sm leading-7 text-slate-500">Permintaan belum diproses karena token keamanan tidak sesuai atau sesi sudah berakhir. Buka kembali halaman sebelum mengirim formulir lagi.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('home') }}" class="btn-secondary">Kembali ke beranda</a>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn">Keluar dari akun</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn">Masuk kembali</a>
                @endauth
            </div>
        </section>
    </main>
@endsection
