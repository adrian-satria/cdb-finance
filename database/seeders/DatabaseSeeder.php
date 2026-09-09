<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProjectSeeder::class,
            MasterBudgetSeeder::class,
            AdminUserSeeder::class,
            SystemSettingSeeder::class,
        ]);
    }
}