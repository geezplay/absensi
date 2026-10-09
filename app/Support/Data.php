<?php

namespace App\Support;

class Data
{
    public const STATUS = [
        'H' => 'Hadir',
        'I' => 'Izin',
        'S' => 'Sakit',
        'A' => 'Alfa',
    ];

    public const MAPEL_POOL = [
        'Matematika',
        'Bahasa Indonesia',
        'Fisika',
        'Kimia',
        'Biologi',
        'Sejarah',
        'Bahasa Inggris',
        'Ekonomi',
    ];

    public const NAMA_POOL = [
        'Adi Nugroho', 'Bella Safira', 'Candra Wijaya', 'Dewi Lestari', 'Eko Prasetyo',
        'Fitri Handayani', 'Galih Ramadhan', 'Hana Pertiwi', 'Irfan Maulana', 'Jihan Aulia',
        'Kevin Sanjaya', 'Larasati Putri', 'Muhammad Rizki', 'Nadia Kusuma', 'Oktavian Dwi',
        'Putri Melati', 'Rangga Saputra', 'Sinta Maharani', 'Taufik Hidayat', 'Umi Kalsum',
        'Vina Anggraini', 'Wahyu Setiawan', 'Xena Anjani', 'Yoga Pratama', 'Zahra Nabila',
        'Agus Salim', 'Bunga Citra', 'Dimas Aditya', 'Elsa Novianti', 'Fajar Sidiq',
        'Gita Gutawa', 'Hendra Gunawan', 'Indah Permata', 'Joko Susilo', 'Kartika Sari',
        'Lukman Hakim', 'Maya Anggita', 'Naufal Akbar', 'Oscar Law', 'Prita Amelia',
    ];

    public static function tahunAjaranSekarang(): string
    {
        return '2026/2027';
    }

    /**
     * Hitung rekap H/I/S/A per siswa.
     *
     * @param  iterable  $siswa   daftar siswa (array atau model, akses ['id'], ['nis'], ['nama'])
     * @param  iterable  $sesi    daftar sesi (akses ['id'])
     * @param  array     $absensi [sesi_id => [siswa_id => 'H'|'I'|'S'|'A']]
     */
    public static function rekap(iterable $siswa, iterable $sesi, array $absensi): array
    {
        $rows = [];
        foreach ($siswa as $s) {
            $rows[$s['id']] = [
                'id' => $s['id'],
                'nis' => $s['nis'],
                'nama' => $s['nama'],
                'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alfa' => 0, 'total' => 0,
            ];
        }

        foreach ($sesi as $se) {
            $map = $absensi[$se['id']] ?? [];
            foreach ($map as $siswaId => $status) {
                if (! isset($rows[$siswaId])) {
                    continue;
                }
                $rows[$siswaId]['total']++;
                match ($status) {
                    'H' => $rows[$siswaId]['hadir']++,
                    'I' => $rows[$siswaId]['izin']++,
                    'S' => $rows[$siswaId]['sakit']++,
                    'A' => $rows[$siswaId]['alfa']++,
                    default => null,
                };
            }
        }

        return array_values($rows);
    }

    /**
     * Bangun map [sesi_id => [siswa_id => status]] untuk sekumpulan sesi.
     */
    public static function absensiMap(iterable $sessionIds): array
    {
        $ids = collect($sessionIds)->all();
        if (empty($ids)) {
            return [];
        }

        return \App\Models\Absensi::whereIn('sesi_id', $ids)
            ->get()
            ->groupBy('sesi_id')
            ->map(fn ($group) => $group->pluck('status', 'siswa_id')->all())
            ->all();
    }
}
