<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Absensi Sekolah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body>
<div class="flex min-h-dvh items-center justify-center bg-gradient-to-br from-brand-600 via-brand-700 to-slate-900 px-4 py-10">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center text-white">
            <span class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-white/15 text-white backdrop-blur">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-7 w-7"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
            <h1 class="text-2xl font-bold tracking-tight">Absensi Sekolah</h1>
            <p class="mt-1 text-sm text-white/70">Pencatatan kehadiran manual oleh guru</p>
        </div>

        <div class="card p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900">Masuk ke akun</h2>
            <p class="mb-5 mt-1 text-sm text-slate-500">Hanya guru & super admin. Siswa tidak login.</p>

            @if (session('ok'))
                <div class="alert-ok mb-4">{{ session('ok') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert-error mb-4">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="/login" class="space-y-4">
                @csrf
                <div>
                    <label class="label" for="email">Email / username</label>
                    <input class="input" id="email" name="email" type="text" value="{{ old('email', 'guru@sma.id') }}" autocomplete="username" required>
                </div>
                <div>
                    <label class="label" for="password">Password</label>
                    <input class="input" id="password" name="password" type="password" value="password" autocomplete="current-password" required>
                </div>
                <button class="btn btn-primary w-full py-3">Masuk</button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-500">
                Lupa password? <a href="/minta-reset" class="font-semibold text-brand-600 hover:underline">Minta reset ke admin</a>
            </p>

            <div class="mt-5 rounded-xl bg-slate-50 p-3.5 text-xs leading-relaxed text-slate-500">
                <p class="font-semibold text-slate-700">Akun demo (mock, klik untuk isi):</p>
                <div class="mt-1.5 flex flex-wrap gap-1.5">
                    <button type="button" onclick="document.getElementById('email').value='guru@sma.id';document.getElementById('password').value='password'" class="rounded-lg bg-white px-2.5 py-1 font-medium text-brand-700 ring-1 ring-slate-200">Guru · guru@sma.id</button>
                    <button type="button" onclick="document.getElementById('email').value='guru2@sma.id';document.getElementById('password').value='password'" class="rounded-lg bg-white px-2.5 py-1 font-medium text-brand-700 ring-1 ring-slate-200">Guru 2 · guru2@sma.id</button>
                    <button type="button" onclick="document.getElementById('email').value='admin@sma.id';document.getElementById('password').value='password'" class="rounded-lg bg-white px-2.5 py-1 font-medium text-brand-700 ring-1 ring-slate-200">Admin · admin@sma.id</button>
                </div>
                <p class="mt-1.5">Password semua akun: <code class="font-semibold">password</code></p>
            </div>
        </div>

        <p class="mt-4 text-center text-xs text-white/50">Mode front-end mock — tanpa backend sungguhan.</p>
    </div>
</div>
</body>
</html>
