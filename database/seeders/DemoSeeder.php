<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\Kelas;
use App\Models\Sesi;
use App\Models\Siswa;
use App\Models\User;
use App\Support\Data;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $guru1 = $this->user('Budi Santoso, S.Pd', 'guru@sma.id');
            $guru2 = $this->user('Siti Aminah, M.Pd', 'guru2@sma.id');
            $this->user('Admin Sekolah', 'admin@sma.id', 'super_admin');

            $k1 = $this->kelas($guru1, 'XII IPA 1', 'XII', 'Matematika', 'Kelas unggulan MIPA');
            $k2 = $this->kelas($guru1, 'XII IPA 2', 'XII', 'Bahasa Indonesia', '');
            $k3 = $this->kelas($guru2, 'XI IPS 1', 'XI', 'Ekonomi', '');
            $k4 = $this->kelas($guru1, 'XII IPA 1', 'XII', 'Fisika', 'Mapel kedua di kelas paralel');

            $this->buatSiswa($k1, 40);
            $this->buatSiswa($k2, 40);
            $this->buatSiswa($k3, 36);
            $this->buatSiswa($k4, 40);

            $s1 = $this->sesi($k1, 'Matematika', now()->subDays(14), '07:00', '08:30', 'Limit Fungsi', 'final', $guru1->id);
            $s2 = $this->sesi($k1, 'Matematika', now()->subDays(7), '08:30', '10:00', 'Hukum Newton', 'final', $guru1->id);
            $this->sesi($k1, 'Matematika', now(), '07:00', '08:30', 'Turunan', 'draft', $guru1->id);
            $this->sesi($k2, 'Bahasa Indonesia', now()->subDays(3), '10:00', '11:30', 'Teks Eksposisi', 'final', $guru1->id);
            $this->sesi($k3, 'Ekonomi', now()->subDays(5), '07:00', '08:30', 'Permintaan & Penawaran', 'draft', $guru2->id);
            $this->sesi($k4, 'Fisika', now()->subDays(2), '13:00', '14:30', 'Gerak Lurus', 'draft', $guru1->id);

            foreach ([$s1, $s2] as $sesi) {
                foreach ($sesi->kelas->siswa as $sw) {
                    $r = ($sw->id + $sesi->id) % 17;
                    Absensi::create([
                        'sesi_id' => $sesi->id,
                        'siswa_id' => $sw->id,
                        'status' => $r === 0 || $r === 5 ? 'A' : ($r === 3 || $r === 11 ? 'I' : ($r === 7 ? 'S' : 'H')),
                        'updated_by' => $guru1->id,
                    ]);
                }
            }

            $nama12 = $s1->kelas->siswa()->orderBy('id')->skip(11)->first()?->nama ?? 'Siswa';

            AuditLog::create(['waktu' => now()->subDays(14)->setTime(8, 45), 'actor_id' => $guru1->id, 'aksi' => 'Finalisasi', 'sesi_id' => $s1->id, 'sesudah' => 'Sesi difinalisasi']);
            AuditLog::create(['waktu' => now()->subDays(13)->setTime(9, 10), 'actor_id' => $guru1->id, 'aksi' => 'Buka Ulang', 'sesi_id' => $s1->id, 'sebelum' => 'Final', 'sesudah' => 'Draft', 'alasan' => 'Salah menandai siswa nomor urut 12, seharusnya Izin bukan Alfa']);
            AuditLog::create(['waktu' => now()->subDays(13)->setTime(9, 12), 'actor_id' => $guru1->id, 'aksi' => 'Ubah Absensi', 'sesi_id' => $s1->id, 'sebelum' => $nama12.': Alfa', 'sesudah' => $nama12.': Izin', 'alasan' => 'Koreksi keterangan izin dari orang tua']);
            AuditLog::create(['waktu' => now()->subDays(13)->setTime(9, 20), 'actor_id' => $guru1->id, 'aksi' => 'Finalisasi', 'sesi_id' => $s1->id, 'sesudah' => 'Sesi difinalisasi']);
            AuditLog::create(['waktu' => now()->subDays(7)->setTime(10, 15), 'actor_id' => $guru1->id, 'aksi' => 'Finalisasi', 'sesi_id' => $s2->id, 'sesudah' => 'Sesi difinalisasi']);
        });
    }

    private function user(string $nama, string $email, string $role = 'guru'): User
    {
        return User::firstOrCreate(
            ['email' => $email],
            ['nama' => $nama, 'password' => Hash::make('password'), 'role' => $role, 'aktif' => true, 'must_change_password' => false],
        );
    }

    private function kelas(User $guru, string $nama, string $tingkat, string $mapel, string $keterangan): Kelas
    {
        return Kelas::firstOrCreate(
            ['owner_guru_id' => $guru->id, 'nama' => $nama, 'mapel' => $mapel],
            ['tingkat' => $tingkat, 'tahun_ajaran' => Data::tahunAjaranSekarang(), 'wali_kelas' => $guru->nama, 'keterangan' => $keterangan, 'status' => 'aktif'],
        );
    }

    private function buatSiswa(Kelas $kelas, int $jumlah): void
    {
        if ($kelas->siswa()->exists()) {
            return;
        }

        $pool = Data::NAMA_POOL;
        $offset = crc32($kelas->id) % count($pool);
        for ($i = 0; $i < $jumlah; $i++) {
            Siswa::create([
                'kelas_id' => $kelas->id,
                'nis' => '2026'.str_pad((string) $kelas->id, 2, '0', STR_PAD_LEFT).str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'nama' => $pool[($offset + $i) % count($pool)],
                'aktif' => true,
            ]);
        }
    }

    private function sesi(Kelas $kelas, string $mapel, $tanggal, string $mulai, string $selesai, string $topik, string $status, int $by): Sesi
    {
        return Sesi::firstOrCreate(
            ['kelas_id' => $kelas->id, 'tanggal' => $tanggal->toDateString(), 'jam_mulai' => $mulai],
            ['mapel' => $mapel, 'jam_selesai' => $selesai, 'topik' => $topik, 'status' => $status, 'created_by' => $by],
        );
    }
}
