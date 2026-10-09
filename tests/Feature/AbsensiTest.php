<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Support\Data;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Rap2hpoutre\FastExcel\FastExcel;
use Tests\TestCase;

class AbsensiTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function loginGuru(): void
    {
        $this->post('/login', ['email' => 'guru@sma.id', 'password' => 'password'])->assertRedirect('/dashboard');
    }

    public function test_guru_login_dan_terisolasi_dari_kelas_guru_lain(): void
    {
        $this->post('/login', ['email' => 'guru@sma.id', 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk();
        $this->get('/kelas/1')->assertOk();
        $this->get('/kelas/3')->assertForbidden();
        $this->get('/admin')->assertForbidden();
    }

    public function test_super_admin_masuk_area_admin(): void
    {
        $this->post('/login', ['email' => 'admin@sma.id', 'password' => 'password'])
            ->assertRedirect('/admin');

        $this->get('/admin')->assertOk();
        $this->get('/admin/rekap')->assertOk();
    }

    public function test_rekap_menjumlahkan_status_semua_sesi_final(): void
    {
        $kelas = Kelas::where('nama', 'XII IPA 1')->where('mapel', 'Matematika')->first();
        $sesi = $kelas->sesi()->get();

        $rows = Data::rekap(
            $kelas->siswa()->get(),
            $sesi,
            Data::absensiMap($sesi->pluck('id')),
        );

        $this->assertCount(40, $rows);
        $this->assertSame(80, array_sum(array_column($rows, 'total')));
        foreach ($rows as $row) {
            $this->assertSame($row['total'], $row['hadir'] + $row['izin'] + $row['sakit'] + $row['alfa']);
        }
    }

    public function test_kelas_hanya_satu_mapel_dan_tolak_kombo_ganda(): void
    {
        $this->loginGuru();

        $this->post('/kelas', [
            'nama' => 'XII IPA 1',
            'tingkat' => 'XII',
            'tahun_ajaran' => '2026/2027',
            'mapel' => 'Matematika',
        ])->assertSessionHasErrors('mapel');

        $this->post('/kelas', [
            'nama' => 'XII IPA 9',
            'tingkat' => 'XII',
            'tahun_ajaran' => '2026/2027',
            'mapel' => 'Biologi',
        ])->assertRedirect();

        $this->get('/kelas?q=biologi')->assertOk()->assertSee('XII IPA 9');
        $this->get('/kelas?mapel=Biologi')->assertOk()->assertSee('XII IPA 9');
    }

    public function test_form_sesi_mengikuti_mapel_kelas(): void
    {
        $this->loginGuru();

        $this->get('/kelas/1/sesi/tambah')->assertOk()->assertSee('Matematika', false);

        $this->post('/kelas/1/sesi', [
            'tanggal' => date('Y-m-d', strtotime('-14 days')),
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
        ])->assertSessionHasErrors('tanggal');
    }

    public function test_finalisasi_mengunci_dan_reopen_wajib_alasan(): void
    {
        $this->loginGuru();

        $kelas = Kelas::find(1);
        $sesi = $kelas->sesi()->where('status', 'draft')->first();
        $status = [];
        foreach ($kelas->siswa as $sw) {
            $status[$sw->id] = 'H';
        }

        $this->post("/sesi/{$sesi->id}/finalisasi", ['status' => $status])->assertRedirect();
        $this->assertSame('final', $sesi->fresh()->status);

        $this->post("/sesi/{$sesi->id}/reopen", ['alasan' => 'pendek'])->assertSessionHasErrors('alasan');
        $this->assertSame('final', $sesi->fresh()->status);

        $this->post("/sesi/{$sesi->id}/reopen", ['alasan' => 'Alasan koreksi yang panjang sekali'])->assertRedirect();
        $this->assertSame('draft', $sesi->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['sesi_id' => $sesi->id, 'aksi' => 'Buka Ulang']);
    }

    public function test_import_xlsx_asli_dengan_pratinjau_dan_konfirmasi(): void
    {
        $this->loginGuru();

        $path = sys_get_temp_dir().'/uji_import_test.xlsx';
        (new FastExcel(collect([
            ['NIS' => '888001', 'Nama Lengkap' => 'Import A'],
            ['NIS' => '888002', 'Nama Lengkap' => 'Import B'],
            ['NIS' => '888001', 'Nama Lengkap' => 'Duplikat'],
            ['NIS' => '', 'Nama Lengkap' => 'Tanpa NIS'],
        ])))->export($path);

        $file = new UploadedFile($path, 'siswa.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $preview = $this->post('/kelas/1/import/preview', ['file' => $file])->assertOk();
        $preview->assertSee('Import A', false);
        $preview->assertSee('NIS duplikat dalam file', false);
        $stok = $preview->viewData('stok');
        $this->assertNotNull($stok);

        $this->post('/kelas/1/import/confirm', ['stok' => $stok])
            ->assertRedirect('/kelas/1?tab=siswa');

        $this->get('/kelas/1/siswa?q=888001')->assertOk()->assertSee('Import A', false);
    }

    public function test_import_menolak_file_bukan_excel(): void
    {
        $this->loginGuru();

        $path = sys_get_temp_dir().'/bukan_excel.txt';
        file_put_contents($path, 'bukan excel');

        $this->post('/kelas/1/import/preview', [
            'file' => new UploadedFile($path, 'x.txt', 'text/plain', null, true),
        ])->assertSessionHasErrors('file');
    }

    public function test_ekspor_excel_rekap(): void
    {
        $this->loginGuru();

        $response = $this->get('/kelas/1/rekap.xlsx');
        $response->assertOk();
        $this->assertStringContainsString('Absensi_', $response->headers->get('content-disposition'));
        $content = $response->streamedContent();
        $this->assertStringStartsWith('PK', $content);
    }
}
