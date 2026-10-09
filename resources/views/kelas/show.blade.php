@extends('layouts.app')
@section('title', $kelas['nama'])
@section('heading', $kelas['nama'])

@section('content')
@php
    $aktifSesi = collect($sesi)->where('status', 'draft')->count();
@endphp

<div class="mb-4 flex flex-wrap items-center gap-2">
    <span class="badge {{ $kelas['status'] === 'aktif' ? 'badge-final' : 'badge-arsip' }}">{{ ucfirst($kelas['status']) }}</span>
    <span class="text-sm text-slate-500">Tingkat {{ $kelas['tingkat'] }} · {{ $kelas['tahun_ajaran'] }}</span>
    @if ($kelas['wali_kelas'])
        <span class="text-sm text-slate-400">· Wali: {{ $kelas['wali_kelas'] }}</span>
    @endif
</div>

<div class="mb-5 flex flex-wrap items-center gap-2 border-b border-slate-200">
    @foreach (['siswa' => 'Siswa ('.count($siswa).')', 'sesi' => 'Sesi ('.count($sesi).')', 'rekap' => 'Rekap'] as $key => $label)
        <a href="/kelas/{{ $kelas['id'] }}?tab={{ $key }}" class="tab {{ $tab === $key ? 'tab-active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

@if ($tab === 'siswa')
    @include('kelas.tabs.siswa', ['kelas' => $kelas, 'siswa' => $siswa, 'q' => $q ?? null])
@elseif ($tab === 'sesi')
    @include('kelas.tabs.sesi', ['kelas' => $kelas, 'sesi' => $sesi])
@else
    @include('kelas.tabs.rekap', ['kelas' => $kelas])
@endif
@endsection
