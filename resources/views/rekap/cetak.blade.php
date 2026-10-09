<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap {{ $kelas->nama }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; color: #111; margin: 24px; }
        .kop { text-align: center; border-bottom: 3px double #111; padding-bottom: 10px; margin-bottom: 14px; }
        .kop h1 { font-size: 16pt; margin: 0; }
        .kop p { margin: 2px 0; font-size: 11pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #111; padding: 5px 8px; }
        th { background: #eee; }
        .center { text-align: center; }
        .ttd { display: flex; justify-content: space-between; margin-top: 28px; text-align: center; }
        .ttd div { width: 40%; }
        @media print { .no-print { display: none; } }
        .no-print { margin-top: 14px; }
        .no-print button { font-size: 12pt; padding: 8px 20px; cursor: pointer; }
    </style>
</head>
<body>
<div class="kop">
    <h1>REKAP KEHADIRAN SISWA</h1>
    <p>Kelas {{ $kelas->nama }} · {{ $kelas->mapel }} · Tahun Ajaran {{ $kelas->tahun_ajaran }}</p>
    <p>Periode {{ date('d M Y', strtotime($dari)) }} – {{ date('d M Y', strtotime($sampai)) }}</p>
</div>

<table>
    <thead>
    <tr><th>No</th><th>NIS</th><th>Nama Lengkap</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alfa</th><th>% Hadir</th></tr>
    </thead>
    <tbody>
    @foreach ($rows as $i => $r)
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td>{{ $r['nis'] }}</td>
            <td>{{ $r['nama'] }}</td>
            <td class="center">{{ $r['hadir'] }}</td>
            <td class="center">{{ $r['izin'] }}</td>
            <td class="center">{{ $r['sakit'] }}</td>
            <td class="center">{{ $r['alfa'] }}</td>
            <td class="center">{{ $r['total'] ? round($r['hadir'] / $r['total'] * 100, 1) : 0 }}%</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="ttd">
    <div>
        Mengetahui,<br>Kepala Sekolah<br><br><br><br><br>( ........................................ )
    </div>
    <div>
        Wali Kelas / Guru Mapel<br>{{ $kelas->wali_kelas ?: $kelas->guru?->nama }}<br><br><br><br><br>( ........................................ )
    </div>
</div>

<div class="no-print" style="text-align:center">
    <button onclick="window.print()">Cetak / Simpan sebagai PDF</button>
</div>
</body>
</html>
