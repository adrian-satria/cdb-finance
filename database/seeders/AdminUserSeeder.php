<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $username = 'admin_keuangan';
        
        $user = DB::table('users')->where('username', $username)->first();
        
        if (!$user) {
            $id_user = DB::table('users')->insertGetId([
                'nama' => 'Adrian Admin',
                'username' => $username,
                'password' => Hash::make('admin123'),
            ]);
        } else {
            $id_user = $user->id_user;
            DB::table('users')->where('id_user', $id_user)->update([
                'password' => Hash::make('admin123')
            ]);
        }

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