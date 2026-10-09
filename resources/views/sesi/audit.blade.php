@extends('layouts.app')
@section('title', 'Audit Sesi')
@section('heading', 'Audit Log Sesi')

@section('content')
<div class="mb-4">
    <a href="/sesi/{{ $sesi['id'] }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800">
        @include('layouts.icon', ['name' => 'arrow-left']) Kembali ke sesi
    </a>
</div>

<div class="card-pad mb-4">
    <p class="font-bold text-slate-900">{{ $sesi['mapel'] }} · {{ $kelas['nama'] }}</p>
    <p class="text-sm text-slate-500">{{ date('d M Y', strtotime($sesi['tanggal'])) }} · {{ $sesi['jam_mulai'] }}–{{ $sesi['jam_selesai'] }}{{ $sesi['topik'] ? ' · '.$sesi['topik'] : '' }}</p>
</div>

@php
    $warna = ['Finalisasi' => 'badge-final', 'Buka Ulang' => 'badge-draft', 'Ubah Absensi' => 'badge-I'];
@endphp

<div class="card-pad">
    @if ($logs->isEmpty())
        <p class="py-8 text-center text-sm text-slate-400">Belum ada catatan audit untuk sesi ini.</p>
    @else
        <ol class="relative ml-2 space-y-5 border-l-2 border-slate-100 pl-6">
            @foreach ($logs as $log)
                <li class="relative">
                    <span class="absolute -left-[31px] top-1 grid h-4 w-4 place-items-center rounded-full bg-white ring-2 ring-brand-500"></span>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="badge {{ $warna[$log['aksi']] ?? 'badge-draft' }}">{{ $log['aksi'] }}</span>
                        <span class="text-xs text-slate-400">{{ is_string($log['waktu']) ? $log['waktu'] : $log['waktu']->format('Y-m-d H:i') }}</span>
                        <span class="text-xs font-medium text-slate-500">oleh {{ $log['actor']['nama'] ?? 'Sistem' }}</span>
                    </div>
                    @if ($log['sebelum'] || $log['sesudah'])
                        <p class="mt-1.5 text-sm text-slate-600">
                            <span class="rounded bg-rose-50 px-1.5 py-0.5 text-rose-600">{{ $log['sebelum'] ?? '—' }}</span>
                            <span class="mx-1 text-slate-300">→</span>
                            <span class="rounded bg-emerald-50 px-1.5 py-0.5 text-emerald-600">{{ $log['sesudah'] ?? '—' }}</span>
                        </p>
                    @endif
                    @if ($log['alasan'])
                        <p class="mt-1.5 rounded-lg bg-slate-50 px-3 py-2 text-sm italic text-slate-500">“{{ $log['alasan'] }}”</p>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif
</div>
@endsection
