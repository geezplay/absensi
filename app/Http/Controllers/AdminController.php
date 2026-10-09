<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Kelas;
use App\Models\Sesi;
use App\Models\User;
use App\Support\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $guru = User::where('role', 'guru')->withCount('kelas')->orderBy('nama')->get();

        return view('admin.guru', [
            'user' => $request->user(),
            'guru' => $guru,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        User::create([
            'nama' => $data['nama'],
            'email' => strtolower($data['email']),
            'password' => Hash::make('password'),
            'role' => 'guru',
            'aktif' => true,
            'must_change_password' => true,
        ]);

        return back()->with('ok', 'Akun guru dibuat. Password awal: password (wajib ganti saat login).');
    }

    public function toggle(User $guru)
    {
        if ($guru->role !== 'guru') {
            abort(404);
        }
        $guru->aktif = ! $guru->aktif;
        $guru->save();

        return back()->with('ok', $guru->aktif ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.');
    }

    public function resetPassword(User $guru)
    {
        if ($guru->role !== 'guru') {
            abort(404);
        }
        $guru->password = Hash::make('password');
        $guru->must_change_password = true;
        $guru->save();

        return back()->with('ok', 'Password direset ke "password". Guru wajib ganti saat login.');
    }

    public function rekap(Request $request)
    {
        $data = Kelas::with(['guru', 'siswa', 'sesi'])->withCount(['siswa', 'sesi'])->orderBy('nama')->get()
            ->map(function (Kelas $k) {
                $absensiMap = Data::absensiMap($k->sesi->pluck('id'));
                $rows = Data::rekap($k->siswa, $k->sesi, $absensiMap);
                $hadir = array_sum(array_column($rows, 'hadir'));
                $total = array_sum(array_column($rows, 'total'));

                return [
                    'kelas' => $k,
                    'guru' => $k->guru,
                    'jumlah_siswa' => $k->siswa_count,
                    'jumlah_sesi' => $k->sesi_count,
                    'persen' => $total ? round($hadir / $total * 100, 1) : 0,
                ];
            });

        return view('admin.rekap', [
            'user' => $request->user(),
            'data' => $data,
        ]);
    }

    public function audit(Request $request)
    {
        $logs = AuditLog::with(['actor', 'sesi'])->orderByDesc('waktu')->limit(500)->get();

        return view('admin.audit', [
            'user' => $request->user(),
            'logs' => $logs,
        ]);
    }
}
