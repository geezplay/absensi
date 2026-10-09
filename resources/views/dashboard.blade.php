@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="grid gap-4 sm:grid-cols-3">
    <div class="card-pad">
        <p class="stat-label">Kelas Saya</p>
        <p class="stat-value mt-1">{{ $jumlahKelas }}</p>
        <a href="/kelas" class="mt-2 inline-block text-sm font-semibold text-brand-600 hover:underline">Kelola kelas →</a>
    </div>
    <div class="card-pad">
        <p class="stat-label">Sesi Bulan Ini</p>
        <p class="stat-value mt-1">{{ $jumlahSesiBulanIni }}</p>
        <p class="mt-2 text-sm text-slate-400">{{ count($draft) }} draft belum final</p>
    </div>
    <div class="card-pad">
        <p class="stat-label">Total Siswa</p>
        <p class="stat-value mt-1">{{ $totalSiswa }}</p>
        <p class="mt-2 text-sm text-slate-400">di semua kelas Anda</p>
    </div>
</div>

@if (count($draft) > 0)
    <div class="card-pad mt-4 border-amber-200 bg-amber-50">
        <div class="flex items-start gap-3">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-amber-200 text-amber-700">
                @include('layouts.icon', ['name' => 'alert'])
            </span>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-amber-800">{{ count($draft) }} sesi draft belum difinalisasi</p>
                <div class="mt-2 space-y-1.5">
                    @foreach ($draft as $s)
                        <a href="/sesi/{{ $s['id'] }}" class="block truncate text-sm font-medium text-amber-700 hover:underline">
                            {{ $s['mapel'] }} · {{ date('d M Y', strtotime($s['tanggal'])) }} {{ $s['jam_mulai'] }} → Lanjut isi
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

<div class="mt-4 grid gap-4 lg:grid-cols-5">
    <div class="card-pad lg:col-span-3">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="font-bold text-slate-900">Kelas saya</h2>
            <a href="/kelas/tambah" class="btn btn-primary btn-sm">@include('layouts.icon', ['name' => 'plus']) Tambah Kelas</a>
        </div>
        @if (empty($kelasSaya))
            <div class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-500">
                Belum ada kelas. <a href="/kelas/tambah" class="font-semibold text-brand-600 hover:underline">Buat kelas pertama</a>.
            </div>
        @else
            <div class="space-y-2">
                @foreach ($kelasSaya as $k)
                    <a href="/kelas/{{ $k['id'] }}" class="flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:border-brand-300 hover:bg-brand-50/40">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-100 text-brand-700">
                            @include('layouts.icon', ['name' => 'book'])
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-slate-900">{{ $k['nama'] }}</p>
                            <p class="truncate text-xs text-slate-400">{{ $k['tingkat'] }} · {{ $k['tahun_ajaran'] }} · {{ $k['mapel'] }}</p>
                        </div>
                        @if ($k['status'] === 'arsip')
                            <span class="badge badge-arsip">Arsip</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card-pad lg:col-span-2">
        <h2 class="mb-1 font-bold text-slate-900">Perlu perhatian</h2>
        <p class="mb-3 text-sm text-slate-400">5 siswa alfa terbanyak (semua kelas)</p>
        @if ($topAlfa->isEmpty())
            <div class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-500">Belum ada data alfa. Bagus!</div>
        @else
            <div class="space-y-2">
                @foreach ($topAlfa as $row)
                    <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-3 {{ $row['jumlah'] >= 3 ? 'border-rose-200 bg-rose-50/60' : '' }}">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">
                            {{ strtoupper(substr($row['siswa']['nama'], 0, 1)) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-800 {{ $row['jumlah'] >= 3 ? 'text-rose-700' : '' }}">{{ $row['siswa']['nama'] }}</p>
                            <p class="truncate text-xs text-slate-400">{{ $row['kelas']['nama'] }} · NIS {{ $row['siswa']['nis'] }}</p>
                        </div>
                        <span class="badge {{ $row['jumlah'] >= 3 ? 'badge-A' : 'badge-draft' }}">{{ $row['jumlah'] }}× Alfa</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
