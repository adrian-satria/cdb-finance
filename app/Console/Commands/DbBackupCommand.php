<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Config;

class DbBackupCommand extends Command
{
    protected $signature = 'db:backup
        {--keep=30 : Jumlah salinan terjaga}
        {--path= : Override direktori tujuan (luar webroot)}';

    protected $description = 'Backup database (mysqldump + gzip) ke direktori luar webroot';

    public function handle(): int
    {
        $cfg = Config::get('database.connections.mysql');
        $host = $cfg['host'] ?? '127.0.0.1';
        $port = $cfg['port'] ?? 3306;
        $database = $cfg['database'];
        $user = $cfg['username'];
        $pass = $cfg['password'];

        $dir = $this->resolveDir();
        if (! $dir) {
            return self::FAILURE;
        }
        File::ensureDirectoryExists($dir);

        $stamp = now()->format('Ymd_His');
        $file = $dir.'/db_'.$database.'_'.$stamp.'.sql.gz';

        $mysqldump = Config::get('backup.mysqldump_path', 'mysqldump');

        $this->info("Menjalankan mysqldump -> {$file}");
        $args = [
            $mysqldump,
            '--host='.$host,
            '--port='.$port,
            '--single-transaction',
            '--no-tablespaces',
            '-u'.$user,
        ];
        if ($pass !== '' && $pass !== null) {
            $args[] = '-p'.$pass;
        }
        $args[] = $database;

        $process = Process::timeout(900)->command($args);

        $result = $process->run();

        if (! $result->successful()) {
            $this->error('mysqldump gagal: '.$result->errorOutput());

            return self::FAILURE;
        }

        File::put($file, gzencode($result->output(), 9));
        $this->info('Backup selesai: '.round(File::size($file) / 1024, 1).' KB');

        $this->rotate((int) $this->option('keep'), $dir, $database);

        return self::SUCCESS;
    }

    private function resolveDir(): ?string
    {
        $path = $this->option('path') ?: Config::get('backup.external_path', base_path('../backups'));

        if (File::isWritable($path) || File::makeDirectory($path, 0755, true, true)) {
            return rtrim($path, '/\\');
        }

        // Fallback ke storage lokal bila path eksternal tak tersedia (mis. dev Windows)
        $fallback = storage_path('app/backups');
        $this->warn("Path eksternal tak bisa ditulis ({$path}), fallback ke {$fallback}");
        File::ensureDirectoryExists($fallback);

        return $fallback;
    }

    private function rotate(int $keep, string $dir, string $database): void
    {
        $files = glob($dir.'/db_'.$database.'_*.sql.gz');
        if ($files === false || count($files) <= $keep) {
            return;
        }

        usort($files, fn ($a, $b) => strcmp($a, $b));
        $remove = array_slice($files, 0, count($files) - $keep);
        foreach ($remove as $f) {
            File::delete($f);
        }
        $this->info('Rotasi: '.count($remove).' salinan lama dihapus (keep='.$keep.')');
    }
}
