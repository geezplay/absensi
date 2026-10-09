@php
    use App\Support\Data;
    $siswaTab = $kelas->siswa()->orderBy('nama')->get();
    $sesiTab = $kelas->sesi()->get();
    $rows = Data::rekap($siswaTab, $sesiTab, Data::absensiMap($sesiTab->pluck('id')));
    $totHadir = array_sum(array_column($rows, 'hadir'));
    $totIzin = array_sum(array_column($rows, 'izin'));
    $totSakit = array_sum(array_column($rows, 'sakit'));
    $totAlfa = array_sum(array_column($rows, 'alfa'));
    $totAll = array_sum(array_column($rows, 'total'));
    $pct = fn ($n) => $totAll ? round($n / $totAll * 100, 1) : 0;
@endphp

<div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
    <div class="card-pad"><p class="stat-label">Hadir</p><p class="text-2xl font-bold text-emerald-600">{{ $pct($totHadir) }}%</p><p class="text-xs text-slate-400">{{ $totHadir }} dari {{ $totAll }}</p></div>
    <div class="card-pad"><p class="stat-label">Izin</p><p class="text-2xl font-bold text-amber-600">{{ $pct($totIzin) }}%</p><p class="text-xs text-slate-400">{{ $totIzin }} baris</p></div>
    <div class="card-pad"><p class="stat-label">Sakit</p><p class="text-2xl font-bold text-sky-600">{{ $pct($totSakit) }}%</p><p class="text-xs text-slate-400">{{ $totSakit }} baris</p></div>
    <div class="card-pad"><p class="stat-label">Alfa</p><p class="text-2xl font-bold text-rose-600">{{ $pct($totAlfa) }}%</p><p class="text-xs text-slate-400">{{ $totAlfa }} baris</p></div>
</div>

<div class="mt-4 flex flex-wrap items-center justify-between gap-2">
    <p class="text-sm text-slate-500">Ringkasan seluruh sesi. Untuk filter periode & ekspor, buka halaman rekap.</p>
    <a href="/kelas/{{ $kelas['id'] }}/rekap" class="btn btn-primary btn-sm">Buka Rekap Lengkap</a>
</div>

<div class="card mt-3">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Nama</th><th>NIS</th><th class="text-center">H</th><th class="text-center">I</th><th class="text-center">S</th><th class="text-center">A</th><th class="text-center">% Hadir</th></tr>
            </thead>
            <tbody>
                @foreach ($rows as $r)
                    @php $persen = $r['total'] ? round($r['hadir'] / $r['total'] * 100, 1) : 0; @endphp
                    <tr class="{{ $r['alfa'] >= 3 ? 'row-alfa' : '' }}">
                        <td class="font-medium text-slate-800">{{ $r['nama'] }}</td>
                        <td class="font-mono text-slate-500">{{ $r['nis'] }}</td>
                        <td class="text-center">{{ $r['hadir'] }}</td>
                        <td class="text-center">{{ $r['izin'] }}</td>
                        <td class="text-center">{{ $r['sakit'] }}</td>
                        <td class="text-center font-semibold {{ $r['alfa'] >= 3 ? 'text-rose-700' : '' }}">{{ $r['alfa'] }}</td>
                        <td class="text-center font-semibold">{{ $persen }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
