@php
    $role = $user['role'] ?? 'guru';
    $items = $role === 'super_admin'
        ? [
            ['label' => 'Guru', 'href' => '/admin', 'active' => request()->is('admin') && ! request()->is('admin/*'), 'icon' => 'users'],
            ['label' => 'Rekap', 'href' => '/admin/rekap', 'active' => request()->is('admin/rekap'), 'icon' => 'chart'],
            ['label' => 'Audit', 'href' => '/admin/audit', 'active' => request()->is('admin/audit'), 'icon' => 'clock'],
        ]
        : [
            ['label' => 'Dashboard', 'href' => '/dashboard', 'active' => request()->is('dashboard'), 'icon' => 'home'],
            ['label' => 'Kelas', 'href' => '/kelas', 'active' => request()->is('kelas*'), 'icon' => 'book'],
        ];
@endphp
<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur lg:hidden">
    <div class="grid {{ count($items) === 2 ? 'grid-cols-2' : 'grid-cols-3' }}">
        @foreach ($items as $item)
            <a href="{{ $item['href'] }}" class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-semibold {{ $item['active'] ? 'text-brand-600' : 'text-slate-400' }}">
                @include('layouts.icon', ['name' => $item['icon']])
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</nav>
