@php $role = $user['role'] ?? 'guru'; @endphp
<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur lg:bg-white">
    <div class="mx-auto flex h-14 w-full max-w-6xl items-center gap-3 px-4 sm:px-6 lg:h-16 lg:px-8">
        <button @click="mobileNav = true" class="btn btn-ghost -ml-2 p-2 lg:hidden" aria-label="Menu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="h-5 w-5"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <h1 class="min-w-0 flex-1 truncate text-base font-semibold text-slate-900 lg:text-lg">
            @yield('heading', $role === 'super_admin' ? 'Super Admin' : 'Dashboard')
        </h1>

        <div class="hidden items-center gap-2 lg:flex">
            <span class="text-sm text-slate-500">{{ $user['nama'] ?? '' }}</span>
            <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-100 text-sm font-bold text-brand-700">
                {{ strtoupper(substr($user['nama'] ?? 'U', 0, 1)) }}
            </span>
        </div>
    </div>
</header>
