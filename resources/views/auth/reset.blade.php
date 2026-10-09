<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Minta Reset · Absensi Sekolah</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body>
<div class="flex min-h-dvh items-center justify-center bg-slate-100 px-4 py-10">
    <div class="w-full max-w-md card-pad">
        <h1 class="text-lg font-bold text-slate-900">Minta reset password</h1>
        <p class="mt-1 text-sm text-slate-500">
            Pada fase 1, reset password dilakukan oleh Super Admin (tanpa email otomatis).
            Hubungi admin sekolah dan sebutkan email akun Anda.
        </p>
        <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
            <p class="font-semibold text-slate-700">Kontak Admin</p>
            <p class="mt-1">admin@sma.id · 021-1234567</p>
        </div>
        <div class="mt-5 flex gap-2">
            <a href="/login" class="btn btn-secondary flex-1">Kembali ke login</a>
            <a href="/demo/reset" class="btn btn-ghost flex-1 text-rose-600 hover:bg-rose-50">Reset data demo</a>
        </div>
    </div>
</div>
</body>
</html>
