<?php

namespace App\Services;

use App\Support\RoleHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileUploadService
{
    const SPP_STORAGE_PATH = 'app/private/lampiran_spp';

    const UM_STORAGE_PATH = 'app/private/lampiran_um';

    const SIGNATURE_STORAGE_PATH = 'app/private/signatures';

    const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    const ALLOWED_SIG_MIMES = [
        'image/jpeg',
        'image/png',
    ];

    /**
     * Upload SPP attachment files (maker or checker).
     *
     * @param  UploadedFile[]  $files
     * @param  string  $kategori  'MAKER' or 'CHECKER'
     * @return array Uploaded file records inserted
     */
    public function uploadSppAttachments(array $files, string $noSurat, string $kategori = 'MAKER'): array
    {
        $records = [];

        foreach ($files as $file) {
            if (! $this->isValidMime($file, self::ALLOWED_MIMES)) {
                throw new \RuntimeException("File {$file->getClientOriginalName()} ditolak: tipe file tidak diizinkan.");
            }

            $ext = $file->extension() ?: $file->getClientOriginalExtension();
            $prefix = strtolower($kategori);
            $namaFile = "{$prefix}_{$noSurat}_{$this->generateUniqueId()}.{$ext}";

            $file->move(storage_path(self::SPP_STORAGE_PATH), $namaFile);

            DB::table('surat_permintaan_files')->insert([
                'no_surat' => $noSurat,
                'nama_file' => $namaFile,
                'kategori' => $kategori,
                'tipe_file' => $ext,
            ]);

            $records[] = [
                'nama_file' => $namaFile,
                'kategori' => $kategori,
                'tipe_file' => $ext,
            ];
        }

        return $records;
    }

    /**
     * Upload user signature image.
     *
     * @return string New signature filename
     */
    public function uploadSignature(UploadedFile $file, int $userId): string
    {
        if (! $this->isValidMime($file, self::ALLOWED_SIG_MIMES)) {
            throw new \RuntimeException("File {$file->getClientOriginalName()} ditolak: tipe file tidak diizinkan.");
        }

        $this->deleteOldSignature($userId);

        $ext = $file->extension() ?: $file->getClientOriginalExtension();
        $newName = "sig_{$userId}_".time()."_{$this->generateUniqueId()}.{$ext}";

        $file->move(storage_path(self::SIGNATURE_STORAGE_PATH), $newName);

        DB::table('users')->where('id_user', $userId)->update([
            'signature_path' => $newName,
            'updated_at' => now(),
        ]);

        return $newName;
    }

    /**
     * Delete old signature file for a user.
     */
    public function deleteOldSignature(int $userId): void
    {
        $old = DB::table('users')->where('id_user', $userId)->value('signature_path');
        if ($old) {
            $oldPath = storage_path(self::SIGNATURE_STORAGE_PATH.'/'.$old);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }
    }

    /**
     * Download SPP attachment with authorization check.
     *
     * @return BinaryFileResponse|null Null if unauthorized
     */
    public function downloadSppFile(
        string $namaFile,
        ?string $role,
        ?string $userArea,
        ?string $userProject
    ): ?BinaryFileResponse {
        $fileRecord = DB::table('surat_permintaan_files as f')
            ->join('surat_permintaan as s', 'f.no_surat', '=', 's.no_surat')
            ->where('f.nama_file', $namaFile)
            ->select('s.kode_area', 's.kode_project', 's.no_surat')
            ->first();

        if (! $fileRecord) {
            return null;
        }

        if (! $this->canAccessFile($role, $userArea, $userProject, $fileRecord)) {
            return null;
        }

        $path = storage_path(self::SPP_STORAGE_PATH.'/'.$namaFile);
        if (! file_exists($path)) {
            return null;
        }

        return response()->download($path);
    }

    /**
     * Check if user can access a specific SPP file.
     *
     * @param  object  $fileRecord  With kode_area, kode_project properties
     */
    public function canAccessFile(?string $role, ?string $userArea, ?string $userProject, object $fileRecord): bool
    {
        return RoleHelper::canAccessSpp($role, $userArea, $userProject, $fileRecord);
    }

    /**
     * Delete a specific SPP file.
     */
    public function deleteSppFile(string $namaFile): bool
    {
        $path = storage_path(self::SPP_STORAGE_PATH.'/'.$namaFile);
        if (file_exists($path)) {
            @unlink($path);
        }

        DB::table('surat_permintaan_files')
            ->where('nama_file', $namaFile)
            ->delete();

        return true;
    }

    /**
     * Get full storage path for an SPP file.
     *
     * @return string|null Null if not found
     */
    public function getSppFilePath(string $namaFile): ?string
    {
        $path = storage_path(self::SPP_STORAGE_PATH.'/'.$namaFile);

        return file_exists($path) ? $path : null;
    }

    /**
     * Validate file extension against allowed types.
     */
    public function isValidFileType(UploadedFile $file, array $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png']): bool
    {
        $ext = strtolower($file->getClientOriginalExtension());

        return in_array($ext, $allowedTypes, true);
    }

    /**
     * Validate file size (in KB).
     *
     * @param  int  $maxSizeKb  Default 5120 (5MB)
     */
    public function isValidFileSize(UploadedFile $file, int $maxSizeKb = 5120): bool
    {
        return $file->getSize() <= ($maxSizeKb * 1024);
    }

    public function uploadAttachments(array $files, string $table, string $refColumn, string $noRef, string $storagePath, string $kategori = 'MAKER'): array
    {
        $records = [];

        foreach ($files as $file) {
            if (! $this->isValidMime($file, self::ALLOWED_MIMES)) {
                throw new \RuntimeException("File {$file->getClientOriginalName()} ditolak: tipe file tidak diizinkan.");
            }

            $ext = $file->extension() ?: $file->getClientOriginalExtension();
            $namaFile = strtolower($kategori)."_{$noRef}_{$this->generateUniqueId()}.{$ext}";

            $file->move(storage_path($storagePath), $namaFile);

            DB::table($table)->insert([
                $refColumn => $noRef,
                'nama_file' => $namaFile,
                'kategori' => $kategori,
                'tipe_file' => $ext,
            ]);

            $records[] = ['nama_file' => $namaFile, 'kategori' => $kategori, 'tipe_file' => $ext];
        }

        return $records;
    }

    public function uploadUangMukaAttachments(array $files, string $noAju, string $kategori = 'MAKER'): array
    {
        return $this->uploadAttachments($files, 'pengajuan_uang_muka_files', 'no_aju', $noAju, self::UM_STORAGE_PATH, $kategori);
    }

    public function downloadUangMukaFile(string $namaFile, ?string $role, ?string $userArea, ?string $userProject): ?BinaryFileResponse
    {
        $fileRecord = DB::table('pengajuan_uang_muka_files as f')
            ->join('pengajuan_uang_muka as u', 'f.no_aju', '=', 'u.no_aju')
            ->where('f.nama_file', $namaFile)
            ->select('u.kode_area', 'u.kode_project', 'u.no_aju')
            ->first();

        if (! $fileRecord || ! RoleHelper::canAccessSpp($role, $userArea, $userProject, $fileRecord)) {
            return null;
        }

        $path = storage_path(self::UM_STORAGE_PATH.'/'.$namaFile);

        return file_exists($path) ? response()->download($path) : null;
    }

    private function isValidMime(UploadedFile $file, array $allowed): bool
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file->getPathname());
        finfo_close($finfo);

        return in_array($mime, $allowed, true);
    }

    private function generateUniqueId(): string
    {
        return bin2hex(random_bytes(8));
    }
}
