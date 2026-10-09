<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Kelas;
use App\Models\Sesi;
use App\Support\Data;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function index(Request $request, Kelas $kelas)
    {
        $dari = $request->query('dari', date('Y-m-01'));
        $sampai = $request->query('sampai', date('Y-m-d'));

        $sesi = $kelas->sesi()
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderByDesc('tanggal')
            ->get();

        $siswa = $kelas->siswa()->orderBy('nama')->get();
        $absensiMap = Data::absensiMap($sesi->pluck('id'));
        $rows = Data::rekap($siswa, $sesi, $absensiMap);

        $ringkas = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alfa' => 0, 'total' => 0];
        foreach ($rows as $r) {
            $ringkas['hadir'] += $r['hadir'];
            $ringkas['izin'] += $r['izin'];
            $ringkas['sakit'] += $r['sakit'];
            $ringkas['alfa'] += $r['alfa'];
            $ringkas['total'] += $r['total'];
        }

        return view('rekap.kelas', [
            'user' => $request->user(),
            'kelas' => $kelas,
            'rows' => $rows,
            'ringkas' => $ringkas,
            'sesi' => $sesi,
            'dari' => $dari,
            'sampai' => $sampai,
            'ambang' => 3,
        ]);
    }

    public function exportExcel(Request $request, Kelas $kelas)
    {
        $dari = $request->query('dari', date('Y-m-01'));
        $sampai = $request->query('sampai', date('Y-m-d'));
        $rows = $this->hitung($kelas, $dari, $sampai);

        $sheet = collect($rows)->map(fn ($r) => [
            'NIS' => $r['nis'],
            'Nama' => $r['nama'],
            'Hadir' => $r['hadir'],
            'Izin' => $r['izin'],
            'Sakit' => $r['sakit'],
            'Alfa' => $r['alfa'],
            'Total Sesi' => $r['total'],
            '% Hadir' => $r['total'] ? round($r['hadir'] / $r['total'] * 100, 1) : 0,
        ]);

        $nama = 'Absensi_'.str_replace(' ', '', $kelas->nama).'_'.$kelas->mapel.'_'.date('Ym', strtotime($dari)).'.xlsx';

        return (new \Rap2hpoutre\FastExcel\FastExcel($sheet))->download($nama);
    }

    public function cetak(Request $request, Kelas $kelas)
    {
        $dari = $request->query('dari', date('Y-m-01'));
        $sampai = $request->query('sampai', date('Y-m-d'));
        $rows = $this->hitung($kelas, $dari, $sampai);

        return view('rekap.cetak', [
            'kelas' => $kelas,
            'rows' => $rows,
            'dari' => $dari,
            'sampai' => $sampai,
        ]);
    }

    private function hitung(Kelas $kelas, string $dari, string $sampai): array
    {
        $sesi = $kelas->sesi()->whereBetween('tanggal', [$dari, $sampai])->get();

        return Data::rekap($kelas->siswa()->orderBy('nama')->get(), $sesi, Data::absensiMap($sesi->pluck('id')));
    }

    public function audit(Request $request, Sesi $sesi)
    {
        $user = $request->user();
        if (! $user->isAdmin() && $sesi->kelas->owner_guru_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $logs = AuditLog::with('actor')->where('sesi_id', $sesi->id)->orderBy('waktu')->get();

        return view('sesi.audit', [
            'user' => $user,
            'sesi' => $sesi,
            'kelas' => $sesi->kelas,
            'logs' => $logs,
        ]);
    }
}
