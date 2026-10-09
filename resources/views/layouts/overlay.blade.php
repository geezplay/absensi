@php
    $role = $user['role'] ?? 'guru';
    $menu = $role === 'super_admin'
        ? [
            ['label' => 'Kelola Guru', 'href' => '/admin', 'icon' => 'users'],
            ['label' => 'Rekap Semua Kelas', 'href' => '/admin/rekap', 'icon' => 'chart'],
            ['label' => 'Audit Log', 'href' => '/admin/audit', 'icon' => 'clock'],
        ]
        : [
            ['label' => 'Dashboard', 'href' => '/dashboard', 'icon' => 'home'],
            ['label' => 'Kelas Saya', 'href' => '/kelas', 'icon' => 'book'],
        ];
@endphp
<div x-cloak x-show="mobileNav" class="fixed inset-0 z-40 lg:hidden">
    <div x-show="mobileNav" x-transition.opacity @click="mobileNav = false" class="absolute inset-0 bg-slate-900/40"></div>
    <div x-show="mobileNav" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
         class="absolute inset-y-0 left-0 flex w-72 flex-col bg-white shadow-xl">
        <div class="flex h-14 items-center gap-2.5 border-b border-slate-200 px-5">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-600 text-white">
                @include('layouts.icon', ['name' => 'check'])
            </span>
            <p class="text-sm font-bold text-slate-900">Absensi Sekolah</p>
        </div>
        <nav class="flex-1 space-y-1 p-3">
            @foreach ($menu as $item)
                <a href="{{ $item['href'] }}" class="nav-link">
                    @include('layouts.icon', ['name' => $item['icon']])
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
        <div class="border-t border-slate-200 p-3">
            <form method="POST" action="/logout">
                @csrf
                <button class="nav-link w-full text-rose-600 hover:bg-rose-50">
                    @include('layouts.icon', ['name' => 'logout'])
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</div>
