@extends('layouts.app')
@section('title', 'Kelas Saya')
@section('heading', 'Kelas Saya')

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-slate-500">{{ count($kelas) }} kelas ditemukan.</p>
    <a href="/kelas/tambah" class="btn btn-primary">@include('layouts.icon', ['name' => 'plus']) Tambah Kelas</a>
</div>

@php
    $mapelOptions = ['Semua mapel' => ''];
    foreach ($daftarMapel as $m) {
        $mapelOptions[$m] = $m;
    }
@endphp
<form method="GET" action="/kelas" class="card-pad mb-4 grid gap-3 sm:grid-cols-[1fr_auto_auto_auto]">
    <div class="relative min-w-0">
        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">@include('layouts.icon', ['name' => 'search'])</span>
        <input class="input pl-10" name="q" value="{{ $q }}" placeholder="Cari nama kelas, mapel, atau tingkat…">
    </div>
    <x-select name="mapel" :options="$mapelOptions" :value="$mapel" submit width="sm:w-44" />
    <x-select name="status" :options="['Semua status' => 'semua', 'Aktif' => 'aktif', 'Arsip' => 'arsip']" :value="$status" submit width="sm:w-36" />
    <div class="flex gap-2">
        <button class="btn btn-primary">Filter</button>
        @if ($q || $mapel || $status !== 'semua')
            <a href="/kelas" class="btn btn-secondary">Reset</a>
        @endif
    </div>
</form>

@if ($kelas->isEmpty())
    <div class="card-pad py-12 text-center">
        <span class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">@include('layouts.icon', ['name' => 'book'])</span>
        <p class="font-semibold text-slate-700">{{ ($q || $mapel || $status !== 'semua') ? 'Tidak ada kelas yang cocok' : 'Belum ada kelas' }}</p>
        <p class="mt-1 text-sm text-slate-500">
            {{ ($q || $mapel || $status !== 'semua') ? 'Coba ubah kata kunci atau filter.' : 'Mulai dengan membuat kelas dan menambahkan siswa.' }}
        </p>
        <a href="{{ ($q || $mapel || $status !== 'semua') ? '/kelas' : '/kelas/tambah' }}" class="btn btn-primary mt-4">
            {{ ($q || $mapel || $status !== 'semua') ? 'Reset Filter' : 'Buat Kelas Pertama' }}
        </a>
    </div>
@else
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($kelas as $k)
            <div class="card-pad flex flex-col">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <a href="/kelas/{{ $k['id'] }}" class="truncate text-lg font-bold text-slate-900 hover:text-brand-700">{{ $k['nama'] }}</a>
                        <p class="text-xs text-slate-400">Tingkat {{ $k['tingkat'] }} · {{ $k['tahun_ajaran'] }}</p>
                    </div>
                    <span class="badge {{ $k['status'] === 'aktif' ? 'badge-final' : 'badge-arsip' }}">{{ $k['status'] === 'aktif' ? 'Aktif' : 'Arsip' }}</span>
                </div>
                <div class="mt-3">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 ring-1 ring-brand-100">
                        @include('layouts.icon', ['name' => 'book']) {{ $k['mapel'] }}
                    </span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3 text-sm">
                    <div><span class="font-bold text-slate-800">{{ $k->siswa_count }}</span> <span class="text-slate-400">siswa</span></div>
                    <div><span class="font-bold text-slate-800">{{ $k->sesi_count }}</span> <span class="text-slate-400">sesi</span></div>
                </div>
                <div class="mt-3 flex gap-2">
                    <a href="/kelas/{{ $k['id'] }}" class="btn btn-secondary btn-sm flex-1">Buka</a>
                    <a href="/kelas/{{ $k['id'] }}/edit" class="btn btn-ghost btn-sm" title="Ubah">@include('layouts.icon', ['name' => 'pencil'])</a>
                    @if ($k['status'] === 'arsip')
                        <form method="POST" action="/kelas/{{ $k['id'] }}/arsip">
                            @csrf
                            <button class="btn btn-secondary btn-sm">Aktifkan</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
