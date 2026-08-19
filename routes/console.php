<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Backup harian 02:00, arsip berkala tiap tanggal 1 pukul 03:00.
// Hosting cron cukup 1 baris: php /path/ke/artisan schedule:run
Schedule::command('db:backup')->dailyAt('02:00');
Schedule::command('data:archive')->monthlyOn(1, '03:00');
