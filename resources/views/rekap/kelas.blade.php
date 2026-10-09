@extends('layouts.app')
@section('title', 'Rekap · '.$kelas['nama'])
@section('heading', 'Rekap · '.$kelas['nama'])

@section('content')
<div class="mb-4">
    <a href="/kelas/{{ $kelas['id'] }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800">
        @include('layouts.icon', ['name' => 'arrow-left']) Kembali ke {{ $kelas['nama'] }}
    </a>
</div>

<form method="GET" class="card-pad mb-4 grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
    <div>
        <label class="label">Dari</label>
        <input class="input" type="date" name="dari" value="{{ $dari }}">
    </div>
    <div>
        <label class="label">Sampai</label>
        <input class="input" type="date" name="sampai" value="{{ $sampai }}">
    </div>
    <div class="flex items-end gap-2">
        <button class="btn btn-primary">Terapkan</button>
        <a href="/kelas/{{ $kelas['id'] }}/rekap.xlsx?dari={{ $dari }}&sampai={{ $sampai }}" class="btn btn-secondary">@include('layouts.icon', ['name' => 'file']) Excel</a>
        <a href="/kelas/{{ $kelas['id'] }}/rekap/cetak?dari={{ $dari }}&sampai={{ $sampai }}" target="_blank" class="btn btn-secondary">@include('layouts.icon', ['name' => 'file']) PDF</a>
    </div>
</form>

@php
    $t = $ringkas['total'];
    $pct = fn ($n) => $t ? round($n / $t * 100, 1) : 0;
@endphp
<div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
    <div class="card-pad"><p class="stat-label">Hadir {{ $pct($ringkas['hadir']) }}%</p><p class="text-2xl font-bold text-emerald-600">{{ $ringkas['hadir'] }}</p></div>
    <div class="card-pad"><p class="stat-label">Izin {{ $pct($ringkas['izin']) }}%</p><p class="text-2xl font-bold text-amber-600">{{ $ringkas['izin'] }}</p></div>
    <div class="card-pad"><p class="stat-label">Sakit {{ $pct($ringkas['sakit']) }}%</p><p class="text-2xl font-bold text-sky-600">{{ $ringkas['sakit'] }}</p></div>
    <div class="card-pad"><p class="stat-label">Alfa {{ $pct($ringkas['alfa']) }}%</p><p class="text-2xl font-bold text-rose-600">{{ $ringkas['alfa'] }}</p></div>
</div>

<div class="mt-3 rounded-xl bg-slate-50 p-3 text-xs text-slate-500">
    Periode {{ date('d M Y', strtotime($dari)) }} – {{ date('d M Y', strtotime($sampai)) }} ·
    {{ count($sesi) }} sesi · Baris merah = alfa ≥ {{ $ambang }} (ambang peringatan). Nama file ekspor: Absensi_{{ str_replace(' ', '', $kelas['nama']) }}_{{ date('Ym', strtotime($dari)) }}.xlsx/pdf
</div>

<div class="card mt-4">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th><th>NIS</th>
                    <th class="text-center">H</th><th class="text-center">I</th><th class="text-center">S</th><th class="text-center">A</th>
                    <th class="text-center">% Hadir</th><th class="text-center">Sesi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $r)
                    @php $p = $r['total'] ? round($r['hadir'] / $r['total'] * 100, 1) : 0; @endphp
                    <tr class="{{ $r['alfa'] >= $ambang ? 'row-alfa' : '' }}">
                        <td class="font-medium text-slate-800">
                            {{ $r['nama'] }}
                            @if ($r['alfa'] >= $ambang)
                                <span class="badge badge-A ml-1">Perhatian</span>
                            @endif
                        </td>
                        <td class="font-mono text-slate-500">{{ $r['nis'] }}</td>
                        <td class="text-center text-emerald-700">{{ $r['hadir'] }}</td>
                        <td class="text-center text-amber-700">{{ $r['izin'] }}</td>
                        <td class="text-center text-sky-700">{{ $r['sakit'] }}</td>
                        <td class="text-center font-bold {{ $r['alfa'] >= $ambang ? 'text-rose-700' : '' }}">{{ $r['alfa'] }}</td>
                        <td class="text-center font-semibold">{{ $p }}%</td>
                        <td class="text-center text-slate-400">{{ $r['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
