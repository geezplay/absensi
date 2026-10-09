<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Sesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect('/admin');
        }

        $kelasSaya = Kelas::where('owner_guru_id', $user->id)
            ->withCount(['siswa', 'sesi'])
            ->orderBy('nama')
            ->get();
        $kelasIds = $kelasSaya->pluck('id');

        $bulanIni = Sesi::whereIn('kelas_id', $kelasIds)
            ->whereYear('tanggal', now()->year)
            ->whereMonth('tanggal', now()->month)
            ->orderBy('tanggal')
            ->get();
        $draft = $bulanIni->where('status', 'draft');

        $topAlfa = Absensi::query()
            ->join('siswa', 'absensi.siswa_id', '=', 'siswa.id')
            ->join('sesi', 'absensi.sesi_id', '=', 'sesi.id')
            ->whereIn('sesi.kelas_id', $kelasIds)
            ->where('absensi.status', 'A')
            ->select('siswa.id as siswa_id', 'siswa.nama', 'siswa.nis', 'siswa.kelas_id', DB::raw('count(*) as jumlah'))
            ->groupBy('siswa.id', 'siswa.nama', 'siswa.nis', 'siswa.kelas_id')
            ->orderByDesc('jumlah')
            ->limit(5)
            ->get();

        $namaKelas = $kelasSaya->pluck('nama', 'id');

        return view('dashboard', [
            'user' => $user,
            'kelasSaya' => $kelasSaya,
            'jumlahKelas' => $kelasSaya->count(),
            'jumlahSesiBulanIni' => $bulanIni->count(),
            'draft' => $draft,
            'totalSiswa' => $kelasSaya->sum('siswa_count'),
            'topAlfa' => $topAlfa->map(fn ($r) => [
                'siswa' => ['id' => $r->siswa_id, 'nama' => $r->nama, 'nis' => $r->nis],
                'kelas' => ['nama' => $namaKelas[$r->kelas_id] ?? ''],
                'jumlah' => (int) $r->jumlah,
            ]),
        ]);
    }
}
