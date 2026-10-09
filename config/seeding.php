<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Akun Super Admin Awal
    |--------------------------------------------------------------------------
    |
    | Dipakai oleh AdminSeeder. Ditaruh di config (bukan env() langsung di
    | seeder) agar tetap terbaca saat config di-cache di produksi.
    | Kosongkan SEED_ADMIN_PASSWORD untuk melewati pembuatan akun.
    |
    */

    'admin_email' => env('SEED_ADMIN_EMAIL', 'admin@absensi.geezplay.site'),
    'admin_password' => env('SEED_ADMIN_PASSWORD'),
];
