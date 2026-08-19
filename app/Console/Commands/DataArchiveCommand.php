<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DataArchiveCommand extends Command
{
    protected $signature = 'data:archive
        {--table= : Hanya arsip satu tabel}
        {--dry-run : Hanya laporkan tanpa memindah/hapus (default bila --force tidak diberi)}
        {--force : Benar-benar pindah & hapus (default: dry-run, hanya laporkan)}
        {--chunk=2000 : Jumlah baris per batch}';

    protected $description = 'Arsip data lewat masa retensi ke cold storage (arsip dulu, purge kemudian)';

    private string $archiveDir;
    private string $lampiranArchiveDir;

    public function handle(): int
    {
        $this->archiveDir = storage_path('app/archive');
        $this->lampiranArchiveDir = storage_path('app/archive/lampiran');
        File::ensureDirectoryExists($this->archiveDir);
        File::ensureDirectoryExists($this->lampiranArchiveDir);

        $force = $this->option('force');
        $onlyTable = $this->option('table');
        $chunk = (int) $this->option('chunk');

        $retention = config('retention', []);
        if ($onlyTable) {
            $retention = [$onlyTable => $retention[$onlyTable] ?? null];
            if (! isset($retention[$onlyTable])) {
                $this->error("Tabel '{$onlyTable}' tidak ada di config/retention.php");

                return self::FAILURE;
            }
        }

        if (! $force) {
            $this->warn('MODE DRY-RUN: tidak ada data yang dipindah/dihapus. Tambah --force untuk eksekusi.');
        }

        $totalMoved = 0;
        foreach ($retention as $table => $retainDays) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            if (! Schema::hasColumn($table, 'created_at')) {
                $this->warn("Lewati {$table}: tidak punya kolom created_at");

                continue;
            }

            $cutoff = now()->subDays((int) $retainDays);
            $eligible = DB::table($table)->where('created_at', '<=', $cutoff)->count();

            if ($eligible === 0) {
                continue;
            }

            $this->info("{$table}: {$eligible} baris lewat retensi (>{$retainDays} hari, sblm {$cutoff->toDateString()})");
            $totalMoved += $eligible;

            if ($force) {
                $this->archiveTable($table, $cutoff, $chunk);
            }
        }

        if ($force) {
            $this->info("Selesai. Total baris dipindah ke arsip: {$totalMoved}");
        } else {
            $this->info("DRY-RUN selesai. Baris yang akan dipindah: {$totalMoved}");
        }

        return self::SUCCESS;
    }

    private function archiveTable(string $table, \Illuminate\Support\Carbon $cutoff, int $chunk): void
    {
        $pk = $this->primaryKey($table);
        $batchId = DB::table('archive_batches')->insertGetId([
            'label' => $table.'_'.now()->format('Ymd_His'),
            'table_name' => $table,
            'rows' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $fileRows = [];
        $moved = 0;

        DB::table($table)->where('created_at', '<=', $cutoff)
            ->orderBy($pk)
            ->chunkById($chunk, function ($rows) use ($table, $pk, $batchId, &$fileRows, &$moved) {
                $keys = [];
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $recordKey = (string) ($rowArray[$pk] ?? null);
                    $keys[] = $recordKey;

                    DB::table('data_archives')->insert([
                        'table_name' => $table,
                        'record_key' => $recordKey,
                        'batch_id' => $batchId,
                        'payload' => json_encode($rowArray, JSON_UNESCAPED_UNICODE),
                        'original_created_at' => $rowArray['created_at'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (str_ends_with($table, '_files') && isset($rowArray['nama_file'])) {
                        $this->moveAttachment((string) $rowArray['nama_file']);
                    }

                    $fileRows[] = $rowArray;
                }

                if ($keys !== []) {
                    DB::table($table)->whereIn($pk, $keys)->delete();
                }
                $moved += count($rows);
            }, $pk);

        $archiveFile = null;
        if ($fileRows !== []) {
            $path = $this->archiveDir.'/'.$table.'_'.now()->format('Ymd_His').'.json.gz';
            File::put($path, gzencode(json_encode($fileRows, JSON_UNESCAPED_UNICODE), 9));
            $archiveFile = 'app/archive/'.basename($path);
            DB::table('data_archives')->where('batch_id', $batchId)->update(['archive_file' => $archiveFile]);
        }

        DB::table('archive_batches')->where('id', $batchId)->update([
            'rows' => $moved,
            'archive_file' => $archiveFile,
            'updated_at' => now(),
        ]);

        $this->info("  -> dipindah {$moved} baris" . ($archiveFile ? " ke {$archiveFile}" : ''));
    }

    private function moveAttachment(string $namaFile): void
    {
        $sources = [
            storage_path('app/private/lampiran_spp/'.$namaFile),
            storage_path('app/private/lampiran_um/'.$namaFile),
            storage_path('app/private/signatures/'.$namaFile),
        ];
        foreach ($sources as $src) {
            if (File::exists($src)) {
                File::move($src, $this->lampiranArchiveDir.'/'.$namaFile);

                return;
            }
        }
    }

    private function primaryKey(string $table): string
    {
        try {
            $row = DB::selectOne("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'");

            return $row?->Column_name ?? 'id';
        } catch (\Throwable) {
            return Schema::hasColumn($table, 'id') ? 'id' : 'no_surat';
        }
    }
}
