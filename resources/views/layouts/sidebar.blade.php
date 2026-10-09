@php
    $role = $user['role'] ?? 'guru';
    $menu = $role === 'super_admin'
        ? [
            ['label' => 'Kelola Guru', 'href' => '/admin', 'active' => request()->is('admin') && ! request()->is('admin/rekap') && ! request()->is('admin/audit'), 'icon' => 'users'],
            ['label' => 'Rekap Semua Kelas', 'href' => '/admin/rekap', 'active' => request()->is('admin/rekap'), 'icon' => 'chart'],
            ['label' => 'Audit Log', 'href' => '/admin/audit', 'active' => request()->is('admin/audit'), 'icon' => 'clock'],
        ]
        : [
            ['label' => 'Dashboard', 'href' => '/dashboard', 'active' => request()->is('dashboard'), 'icon' => 'home'],
            ['label' => 'Kelas Saya', 'href' => '/kelas', 'active' => request()->is('kelas*'), 'icon' => 'book'],
        ];
@endphp

<aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-white lg:flex lg:flex-col">
    <div class="flex h-16 items-center gap-2.5 border-b border-slate-200 px-5">
        <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-600 text-white">
            @include('layouts.icon', ['name' => 'check'])
        </span>
        <div class="leading-tight">
            <p class="text-sm font-bold text-slate-900">Absensi Sekolah</p>
            <p class="text-[11px] font-medium text-slate-400">Fase 1 · Manual Guru</p>
        </div>
    </div>

    <nav class="flex-1 space-y-1 p-3">
        @foreach ($menu as $item)
            <a href="{{ $item['href'] }}" class="nav-link {{ $item['active'] ? 'nav-link-active' : '' }}">
                @include('layouts.icon', ['name' => $item['icon']])
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="border-t border-slate-200 p-3">
        <div class="flex items-center gap-3 rounded-xl px-3 py-2">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand-100 text-sm font-bold text-brand-700">
                {{ strtoupper(substr($user['nama'] ?? 'U', 0, 1)) }}
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-slate-800">{{ $user['nama'] ?? '' }}</p>
                <p class="truncate text-xs text-slate-400">{{ $role === 'super_admin' ? 'Super Admin' : 'Guru' }}</p>
            </div>
        </div>
        <form method="POST" action="/logout" class="mt-1">
            @csrf
            <button class="nav-link w-full text-rose-600 hover:bg-rose-50 hover:text-rose-700">
                @include('layouts.icon', ['name' => 'logout'])
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
