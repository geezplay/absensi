@extends('layouts.app')
@section('title', 'Rekap Semua Kelas')
@section('heading', 'Rekap Semua Kelas (Read-only)')

@section('content')
<p class="mb-4 text-sm text-slate-500">Super Admin memantau seluruh kelas. Pengisian absensi tetap menjadi kewenangan guru pemilik kelas.</p>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($data as $d)
        <div class="card-pad">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="font-bold text-slate-900">{{ $d['kelas']['nama'] }}</p>
                    <p class="text-xs text-slate-400">{{ $d['guru']['nama'] ?? 'Tanpa guru' }}</p>
                </div>
                @if ($d['kelas']['status'] === 'arsip')
                    <span class="badge badge-arsip">Arsip</span>
                @endif
            </div>
            <div class="mt-4 flex items-end justify-between">
                <div>
                    <p class="text-3xl font-bold {{ $d['persen'] >= 80 ? 'text-emerald-600' : ($d['persen'] >= 60 ? 'text-amber-600' : 'text-rose-600') }}">{{ $d['persen'] }}%</p>
                    <p class="text-xs text-slate-400">kehadiran rata-rata</p>
                </div>
                <div class="text-right text-sm text-slate-500">
                    <p><span class="font-semibold text-slate-700">{{ $d['jumlah_siswa'] }}</span> siswa</p>
                    <p><span class="font-semibold text-slate-700">{{ $d['jumlah_sesi'] }}</span> sesi</p>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
