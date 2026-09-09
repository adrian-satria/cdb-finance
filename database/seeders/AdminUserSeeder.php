<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    const ADMIN_USERNAME = 'portfolio_admin';

    public function run(): void
    {
        $username = self::ADMIN_USERNAME;
        $generatePassword = bin2hex(random_bytes(6)) . 'Aa1';

        $user = DB::table('users')->where('username', $username)->first();

        if (! $user) {
            $id_user = DB::table('users')->insertGetId([
                'nama' => 'Portfolio Admin',
                'username' => $username,
                'password' => Hash::make($generatePassword),
            ]);
        } else {
            $id_user = $user->id_user;
            DB::table('users')->where('id_user', $id_user)->update([
                'password' => Hash::make($generatePassword),
            ]);
        }

        echo "[!] DEFAULT ADMIN PASSWORD (change immediately): " . $generatePassword . PHP_EOL;

        // Sekarang menyertakan kolom 'role' demi keamanan hak otorisasi sistem
        DB::table('user_access')->updateOrInsert(
            ['id_user' => $id_user],
            [
                'jabatan' => 'ADMIN',
                'role' => 'ADMIN',
                'kode_area' => 'PUSAT',
            ]
        );
    }
}
