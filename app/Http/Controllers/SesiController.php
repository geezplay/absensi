<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\Kelas;
use App\Models\Sesi;
use App\Support\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SesiController extends Controller
{
    public function create(Request $request, Kelas $kelas)
    {
        return view('sesi.create', [
            'user' => $request->user(),
            'kelas' => $kelas,
            'today' => date('Y-m-d'),
        ]);
    }

    public function store(Request $request, Kelas $kelas)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required'],
            'jam_selesai' => ['required'],
            'topik' => ['nullable', 'string', 'max:150'],
        ]);

        $duplikat = $kelas->sesi()
            ->where('tanggal', $data['tanggal'])
            ->where('jam_mulai', $data['jam_mulai'])
            ->exists();

        if ($duplikat) {
            return back()->withErrors(['tanggal' => 'Sudah ada sesi di tanggal dan jam mulai yang sama di kelas ini.'])->withInput();
        }

        $sesi = $kelas->sesi()->create([
            'mapel' => $kelas->mapel,
            'tanggal' => $data['tanggal'],
            'jam_mulai' => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
            'topik' => $data['topik'] ?? '',
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);

        return redirect('/sesi/'.$sesi->id)->with('ok', 'Sesi dibuat. Isi absensi di bawah.');
    }

    private function authorize(Request $request, Sesi $sesi): Sesi
    {
        $user = $request->user();
        if (! $user->isAdmin() && $sesi->kelas->owner_guru_id !== $user->id) {
            abort(403, 'Akses ditolak: sesi ini bukan milik Anda.');
        }

        return $sesi->load('kelas');
    }

    public function show(Request $request, Sesi $sesi)
    {
        $s = $this->authorize($request, $sesi);
        $kelas = $s->kelas;
        $siswa = $kelas->siswa()->orderBy('nama')->get();
        $absensi = Absensi::where('sesi_id', $s->id)->pluck('status', 'siswa_id')->all();
        $audit = AuditLog::with('actor')->where('sesi_id', $s->id)->orderBy('waktu')->get();

        return view('sesi.show', [
            'user' => $request->user(),
            'kelas' => $kelas,
            'sesi' => $s,
            'siswa' => $siswa,
            'absensi' => $absensi,
            'audit' => $audit,
        ]);
    }

    public function saveDraft(Request $request, Sesi $sesi)
    {
        $s = $this->authorize($request, $sesi);
        if ($s->isFinal()) {
            return back()->withErrors(['absensi' => 'Sesi sudah final. Buka ulang untuk mengubah.']);
        }

        $this->simpanStatus($sesi, $request->user()->id, $request->input('status', []));

        return back()->with('ok', 'Draft absensi disimpan.');
    }

    public function finalize(Request $request, Sesi $sesi)
    {
        $s = $this->authorize($request, $sesi);
        if ($s->isFinal()) {
            return back()->withErrors(['absensi' => 'Sesi sudah final.']);
        }

        $status = $request->input('status', []);
        $belum = [];
        foreach ($s->kelas->siswa as $sw) {
            $val = $status[$sw->id] ?? null;
            if (! $val || ! array_key_exists($val, Data::STATUS)) {
                $belum[] = $sw->nama;
            }
        }

        if (! empty($belum)) {
            return back()->withErrors(['absensi' => count($belum).' siswa belum ditandai: '.implode(', ', array_slice($belum, 0, 3)).(count($belum) > 3 ? ', ...' : '')])->withInput();
        }

        DB::transaction(function () use ($sesi, $request, $status) {
            $this->simpanStatus($sesi, $request->user()->id, $status);
            $sesi->status = 'final';
            $sesi->save();
            $this->catatAudit($sesi->id, $request->user()->id, 'Finalisasi', null, 'Sesi difinalisasi', null);
        });

        return back()->with('ok', 'Sesi difinalisasi dan terkunci.');
    }

    public function reopen(Request $request, Sesi $sesi)
    {
        $request->validate([
            'alasan' => ['required', 'string', 'min:10'],
        ], [
            'alasan.min' => 'Alasan minimal 10 karakter.',
        ]);

        $s = $this->authorize($request, $sesi);
        if (! $s->isFinal()) {
            return back()->withErrors(['alasan' => 'Sesi ini tidak dalam status final.']);
        }

        DB::transaction(function () use ($sesi, $request) {
            $sesi->status = 'draft';
            $sesi->save();
            $this->catatAudit($sesi->id, $request->user()->id, 'Buka Ulang', 'Final', 'Draft', $request->alasan);
        });

        return back()->with('ok', 'Sesi dibuka ulang. Perubahan tercatat di audit log.');
    }

    public function destroy(Request $request, Sesi $sesi)
    {
        $s = $this->authorize($request, $sesi);
        if ($s->isFinal()) {
            return back()->withErrors(['sesi' => 'Sesi final tidak bisa dihapus.']);
        }

        $kelasId = $s->kelas_id;
        $sesi->delete();

        return redirect('/kelas/'.$kelasId.'?tab=sesi')->with('ok', 'Sesi draft dihapus.');
    }

    private function simpanStatus(Sesi $sesi, int $by, array $status): void
    {
        $rows = [];
        foreach ($sesi->kelas->siswa as $sw) {
            $val = $status[$sw->id] ?? null;
            if ($val && array_key_exists($val, Data::STATUS)) {
                $rows[] = ['sesi_id' => $sesi->id, 'siswa_id' => $sw->id, 'status' => $val, 'updated_by' => $by, 'created_at' => now(), 'updated_at' => now()];
            }
        }

        if (empty($rows)) {
            return;
        }

        Absensi::upsert($rows, ['sesi_id', 'siswa_id'], ['status', 'updated_by', 'updated_at']);
    }

    private function catatAudit(int $sesiId, int $actorId, string $aksi, ?string $sebelum, ?string $sesudah, ?string $alasan): void
    {
        AuditLog::create([
            'waktu' => now(),
            'actor_id' => $actorId,
            'aksi' => $aksi,
            'sesi_id' => $sesiId,
            'sebelum' => $sebelum,
            'sesudah' => $sesudah,
            'alasan' => $alasan,
        ]);
    }
}
