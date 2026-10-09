<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    public function index(Request $request, Kelas $kelas)
    {
        $q = trim((string) $request->query('q', ''));
        $siswa = $kelas->siswa()
            ->when($q !== '', function ($w) use ($q) {
                $w->where(fn ($x) => $x->where('nama', 'ILIKE', "%{$q}%")->orWhere('nis', 'ILIKE', "%{$q}%"));
            })
            ->orderBy('nama')
            ->get();

        return view('kelas.show', [
            'user' => $request->user(),
            'kelas' => $kelas,
            'tab' => 'siswa',
            'siswa' => $siswa,
            'sesi' => $kelas->sesi()->orderByDesc('tanggal')->get(),
            'q' => $q,
        ]);
    }

    public function store(Request $request, Kelas $kelas)
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:20', 'unique:siswa,nis,NULL,NULL,kelas_id,'.$kelas->id],
            'nama' => ['required', 'string', 'max:100'],
        ], [
            'nis.unique' => 'NIS sudah dipakai siswa lain di kelas ini.',
        ]);

        $kelas->siswa()->create(['nis' => trim($data['nis']), 'nama' => trim($data['nama']), 'aktif' => true]);

        return back()->with('ok', 'Siswa ditambahkan.');
    }

    public function update(Request $request, Siswa $siswa)
    {
        $this->authorizeSiswa($request, $siswa);

        $data = $request->validate([
            'nis' => ['required', 'string', 'max:20', 'unique:siswa,nis,'.$siswa->id.',id,kelas_id,'.$siswa->kelas_id],
            'nama' => ['required', 'string', 'max:100'],
        ], [
            'nis.unique' => 'NIS sudah dipakai siswa lain di kelas ini.',
        ]);

        $siswa->update(['nis' => trim($data['nis']), 'nama' => trim($data['nama'])]);

        return back()->with('ok', 'Data siswa diperbarui.');
    }

    public function destroy(Request $request, Siswa $siswa)
    {
        $this->authorizeSiswa($request, $siswa);
        $kelasId = $siswa->kelas_id;
        $siswa->delete();

        return redirect('/kelas/'.$kelasId.'?tab=siswa')->with('ok', 'Siswa dihapus.');
    }

    public function importForm(Request $request, Kelas $kelas)
    {
        return view('siswa.import', [
            'user' => $request->user(),
            'kelas' => $kelas,
        ]);
    }

    public function importPreview(Request $request, Kelas $kelas)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv,ods', 'max:2048'],
        ], [
            'file.mimes' => 'File harus .xlsx, .csv, atau .ods (maks 2 MB).',
        ]);

        try {
            $rows = (new \Rap2hpoutre\FastExcel\FastExcel)->import($request->file('file'));
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'File tidak bisa dibaca sebagai Excel/CSV. Pastikan file tidak rusak.']);
        }

        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), array_keys($rows->first() ?? []));
        $headerOk = in_array('nis', $headers, true) && collect($headers)->contains(fn ($h) => str_contains($h, 'nama'));

        if (! $headerOk) {
            return back()->withErrors(['file' => 'Header tidak sesuai. Baris 1 harus berisi kolom "NIS" dan "Nama". Unduh Template untuk contoh.']);
        }

        $data = $rows->take(501)->values()->map(function ($r, $i) {
            $nis = '';
            $nama = '';
            foreach ($r as $k => $v) {
                $key = strtolower(trim((string) $k));
                if ($key === 'nis') {
                    $nis = trim((string) $v);
                } elseif (str_contains($key, 'nama')) {
                    $nama = trim((string) $v);
                }
            }

            return ['baris' => $i + 2, 'nis' => $nis, 'nama' => $nama];
        });

        $nisAda = $kelas->siswa()->pluck('nis')->map(fn ($n) => strtolower($n))->all();
        $terlihat = [];
        $valid = [];
        $gagal = [];
        foreach ($data as $row) {
            $alasan = null;
            if ($row['nis'] === '') {
                $alasan = 'NIS kosong';
            } elseif ($row['nama'] === '') {
                $alasan = 'Nama kosong';
            } elseif (in_array(strtolower($row['nis']), $nisAda, true)) {
                $alasan = 'NIS sudah ada di kelas';
            } elseif (in_array(strtolower($row['nis']), $terlihat, true)) {
                $alasan = 'NIS duplikat dalam file';
            }
            if ($alasan) {
                $gagal[] = array_merge($row, ['alasan' => $alasan]);
            } else {
                $valid[] = $row;
                $terlihat[] = strtolower($row['nis']);
            }
        }

        $stok = sha1($kelas->id.'|'.json_encode($valid).'|'.now()->timestamp);
        cache()->put('import_'.$stok, array_values($valid), now()->addMinutes(30));

        return view('siswa.import-preview', [
            'user' => $request->user(),
            'kelas' => $kelas,
            'valid' => $valid,
            'gagal' => $gagal,
            'stok' => $stok,
            'dipotong' => $rows->count() > 500,
            'namaFile' => $request->file('file')->getClientOriginalName(),
        ]);
    }

    public function importConfirm(Request $request, Kelas $kelas)
    {
        $valid = cache()->get('import_'.$request->input('stok'));

        if (! is_array($valid) || empty($valid)) {
            return redirect('/kelas/'.$kelas->id.'/import')->withErrors(['file' => 'Sesi pratinjau kedaluwarsa (30 menit) atau tidak ada baris valid. Unggah ulang file.']);
        }

        $count = 0;
        DB::transaction(function () use ($valid, $kelas, &$count) {
            foreach ($valid as $row) {
                $siswa = $kelas->siswa()->firstOrCreate(
                    ['nis' => trim($row['nis'])],
                    ['nama' => trim($row['nama']), 'aktif' => true],
                );
                if ($siswa->wasRecentlyCreated) {
                    $count++;
                }
            }
        });
        cache()->forget('import_'.$request->input('stok'));

        return redirect('/kelas/'.$kelas->id.'?tab=siswa')->with('ok', $count.' siswa berhasil diimport.');
    }

    public function template(Kelas $kelas)
    {
        $rows = collect([
            ['NIS' => '202601001', 'Nama Lengkap' => 'Contoh Nama Siswa'],
            ['NIS' => '202601002', 'Nama Lengkap' => 'Contoh Nama Kedua'],
            ['NIS' => '', 'Nama Lengkap' => ''],
            ['NIS' => '', 'Nama Lengkap' => ''],
            ['NIS' => '', 'Nama Lengkap' => ''],
        ]);

        return (new \Rap2hpoutre\FastExcel\FastExcel($rows))
            ->download('Template_Import_Siswa.xlsx');
    }

    private function authorizeSiswa(Request $request, Siswa $siswa): void
    {
        $user = $request->user();
        if (! $user->isAdmin() && $siswa->kelas->owner_guru_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }
    }
}
