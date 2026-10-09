<div class="mb-4 flex flex-wrap items-center justify-between gap-2">
    <p class="text-sm text-slate-500">{{ count($sesi) }} sesi tercatat di kelas ini.</p>
    <a href="/kelas/{{ $kelas['id'] }}/sesi/tambah" class="btn btn-primary btn-sm">@include('layouts.icon', ['name' => 'plus']) Buat Sesi</a>
</div>

@php
    $ordered = collect($sesi)->sortByDesc('tanggal');
@endphp

@if ($ordered->isEmpty())
    <div class="card-pad py-12 text-center">
        <span class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">@include('layouts.icon', ['name' => 'calendar'])</span>
        <p class="font-semibold text-slate-700">Belum ada sesi</p>
        <p class="mt-1 text-sm text-slate-500">Buat sesi pertemuan, lalu isi absensi siswa.</p>
        <a href="/kelas/{{ $kelas['id'] }}/sesi/tambah" class="btn btn-primary mt-4">Buat Sesi Pertama</a>
    </div>
@else
    <div class="grid gap-3 sm:grid-cols-2">
        @foreach ($ordered as $s)
            <a href="/sesi/{{ $s['id'] }}" class="card-pad block transition hover:border-brand-300 hover:shadow">
                <div class="flex items-center justify-between gap-2">
                    <span class="badge {{ $s['status'] === 'final' ? 'badge-final' : 'badge-draft' }}">{{ $s['status'] === 'final' ? 'Final · Terkunci' : 'Draft · Buka' }}</span>
                    <span class="text-xs text-slate-400">{{ date('d M Y', strtotime($s['tanggal'])) }}</span>
                </div>
                <p class="mt-2 font-bold text-slate-900">{{ $s['mapel'] }}</p>
                <p class="text-sm text-slate-500">{{ $s['jam_mulai'] }}–{{ $s['jam_selesai'] }}{{ $s['topik'] ? ' · '.$s['topik'] : '' }}</p>
            </a>
        @endforeach
    </div>
@endif
