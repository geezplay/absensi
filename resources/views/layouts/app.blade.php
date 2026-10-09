<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Absensi Sekolah')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ mobileNav: false, confirmOpen: false, confirmMsg: '', confirmYes: null }"
      @confirm-ask.window="confirmMsg = $event.detail.msg; confirmYes = $event.detail.go; confirmOpen = true">
<div class="min-h-dvh lg:flex">
    @include('layouts.sidebar')

    <div class="min-w-0 flex-1 pb-24 lg:pb-0">
        @include('layouts.topbar')

        <main class="mx-auto w-full max-w-6xl px-4 py-5 sm:px-6 lg:px-8 lg:py-7">
            @if (session('ok'))
                <div class="alert-ok mb-4">{{ session('ok') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert-error mb-4">
                    <ul class="list-disc space-y-0.5 pl-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

@include('layouts.bottomnav')
@include('layouts.overlay')

<div x-cloak x-show="confirmOpen" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
     x-transition.opacity>
    <div x-show="confirmOpen" x-transition.opacity @click="confirmOpen = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
    <div x-show="confirmOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="relative w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="p-5">
            <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full bg-rose-100 text-rose-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
            </div>
            <h3 class="text-center font-bold text-slate-900">Yakin lanjutkan?</h3>
            <p class="mt-1 text-center text-sm text-slate-500" x-text="confirmMsg"></p>
            <div class="mt-5 grid grid-cols-2 gap-2">
                <button type="button" @click="confirmOpen = false" class="btn btn-secondary">Batal</button>
                <button type="button" @click="confirmOpen = false; if (confirmYes) confirmYes()" class="btn btn-primary">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>



{{ $scripts ?? '' }}
@yield('scripts')
</body>
</html>
