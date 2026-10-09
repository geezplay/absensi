<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = (string) config('seeding.admin_password', '');

        if ($password === '') {
            $this->command->warn('AdminSeeder dilewati: SEED_ADMIN_PASSWORD kosong.');

            return;
        }

        $user = User::firstOrCreate(
            ['email' => config('seeding.admin_email')],
            [
                'nama' => 'Admin Sekolah',
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'aktif' => true,
                'must_change_password' => true,
            ],
        );

        $this->command->info(
            $user->wasRecentlyCreated
                ? 'Akun super admin awal dibuat: '.config('seeding.admin_email')
                : 'Akun super admin sudah ada, dilewati: '.config('seeding.admin_email')
        );
    }
}
