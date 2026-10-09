@extends('layouts.app')
@section('title', 'Ganti Password')
@section('heading', 'Ganti Password')

@section('content')
<div class="mx-auto max-w-lg">
    <div class="card-pad">
        @if ($wajib)
            <div class="alert-error mb-4">
                Demi keamanan, Anda <strong>wajib mengganti password</strong> sebelum melanjutkan.
            </div>
        @endif

        <h2 class="text-lg font-bold text-slate-900">Ganti password</h2>
        <p class="mb-5 mt-1 text-sm text-slate-500">Masuk sebagai {{ $user['email'] }}.</p>

        <form method="POST" action="/ganti-password" class="space-y-4">
            @csrf
            <div>
                <label class="label" for="password">Password baru</label>
                <input class="input" id="password" name="password" type="password" required minlength="6">
                <p class="mt-1 text-xs text-slate-400">Minimal 6 karakter.</p>
            </div>
            <div>
                <label class="label" for="password_confirmation">Ulangi password baru</label>
                <input class="input" id="password_confirmation" name="password_confirmation" type="password" required minlength="6">
            </div>
            <button class="btn btn-primary w-full py-3">Simpan password</button>
        </form>
    </div>
</div>
@endsection
