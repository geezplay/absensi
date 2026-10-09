<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Support\Data;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Kelas::where('owner_guru_id', $user->id)->withCount(['siswa', 'sesi']);

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nama', 'ILIKE', "%{$q}%")
                    ->orWhere('mapel', 'ILIKE', "%{$q}%")
                    ->orWhere('tingkat', 'ILIKE', "%{$q}%")
                    ->orWhere('tahun_ajaran', 'ILIKE', "%{$q}%");
            });
        }

        $status = $request->query('status', 'semua');
        if (in_array($status, ['aktif', 'arsip'])) {
            $query->where('status', $status);
        }

        $mapel = trim((string) $request->query('mapel', ''));
        if ($mapel !== '') {
            $query->where('mapel', $mapel);
        }

        $daftarMapel = Kelas::where('owner_guru_id', $user->id)
            ->distinct()->orderBy('mapel')->pluck('mapel');

        return view('kelas.index', [
            'user' => $user,
            'kelas' => $query->orderBy('nama')->get(),
            'q' => $q,
            'status' => $status,
            'mapel' => $mapel,
            'daftarMapel' => $daftarMapel,
        ]);
    }

    public function create(Request $request)
    {
        return view('kelas.form', [
            'user' => $request->user(),
            'kelas' => null,
            'mapelPool' => Data::MAPEL_POOL,
            'tahunAjaran' => Data::tahunAjaranSekarang(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:50'],
            'tingkat' => ['required', 'string', 'in:X,XI,XII'],
            'tahun_ajaran' => ['required', 'string'],
            'wali_kelas' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
            'mapel' => [
                'required', 'string', 'max:50',
                Rule::unique('kelas')->where(fn ($q) => $q->where('owner_guru_id', $user->id)->where('nama', $request->nama)),
            ],
        ], [
            'mapel.required' => 'Pilih satu mata pelajaran untuk kelas ini.',
            'mapel.unique' => 'Anda sudah punya kelas itu untuk mapel tersebut. Ubah nama kelas atau pilih mapel lain.',
        ]);

        $kelas = Kelas::create([
            ...$data,
            'owner_guru_id' => $user->id,
            'status' => 'aktif',
        ]);

        return redirect('/kelas/'.$kelas->id)->with('ok', 'Kelas berhasil dibuat.');
    }

    public function edit(Request $request, Kelas $kelas)
    {
        return view('kelas.form', [
            'user' => $request->user(),
            'kelas' => $kelas,
            'mapelPool' => Data::MAPEL_POOL,
            'tahunAjaran' => Data::tahunAjaranSekarang(),
        ]);
    }

    public function update(Request $request, Kelas $kelas)
    {
        $user = $request->user();
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:50'],
            'tingkat' => ['required', 'string', 'in:X,XI,XII'],
            'tahun_ajaran' => ['required', 'string'],
            'wali_kelas' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
            'mapel' => [
                'required', 'string', 'max:50',
                Rule::unique('kelas')->ignore($kelas->id)->where(fn ($q) => $q->where('owner_guru_id', $user->id)->where('nama', $request->nama)),
            ],
        ], [
            'mapel.unique' => 'Kombinasi kelas + mapel itu sudah Anda miliki.',
        ]);

        $kelas->update($data);

        return redirect('/kelas/'.$kelas->id)->with('ok', 'Perubahan kelas disimpan.');
    }

    public function archive(Kelas $kelas)
    {
        $kelas->status = $kelas->status === 'aktif' ? 'arsip' : 'aktif';
        $kelas->save();

        return back()->with('ok', $kelas->status === 'arsip' ? 'Kelas diarsipkan.' : 'Kelas diaktifkan kembali.');
    }

    public function destroy(Kelas $kelas)
    {
        if ($kelas->sesi()->where('status', 'final')->exists()) {
            return back()->withErrors(['kelas' => 'Kelas tidak bisa dihapus karena sudah punya sesi final. Gunakan Arsip.']);
        }

        $kelas->delete();

        return redirect('/kelas')->with('ok', 'Kelas dihapus.');
    }

    public function show(Request $request, Kelas $kelas)
    {
        $tab = $request->query('tab', 'siswa');
        $kelas->loadCount(['siswa', 'sesi']);

        return view('kelas.show', [
            'user' => $request->user(),
            'kelas' => $kelas,
            'tab' => $tab,
            'siswa' => $kelas->siswa()->orderBy('nama')->get(),
            'sesi' => $kelas->sesi()->orderByDesc('tanggal')->get(),
        ]);
    }
}
