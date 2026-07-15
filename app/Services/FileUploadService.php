<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileUploadService
{
    const SPP_STORAGE_PATH = 'app/private/lampiran_spp';
    const SIGNATURE_STORAGE_PATH = 'app/private/signatures';

    /**
     * Upload SPP attachment files (maker or checker).
     *
     * @param UploadedFile[] $files
     * @param string $noSurat
     * @param string $kategori 'MAKER' or 'CHECKER'
     * @return array Uploaded file records inserted
     */
    public function uploadSppAttachments(array $files, string $noSurat, string $kategori = 'MAKER'): array
    {
        $records = [];

        foreach ($files as $file) {
            $ext = $file->getClientOriginalExtension();
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
     * @param UploadedFile $file
     * @param int $userId
     * @return string New signature filename
     */
    public function uploadSignature(UploadedFile $file, int $userId): string
    {
        $this->deleteOldSignature($userId);

        $ext = $file->getClientOriginalExtension();
        $newName = "sig_{$userId}_" . time() . "_{$this->generateUniqueId()}.{$ext}";

        $file->move(storage_path(self::SIGNATURE_STORAGE_PATH), $newName);

        DB::table('users')->where('id_user', $userId)->update([
            'signature_path' => $newName,
            'updated_at' => now(),
        ]);

        return $newName;
    }

    /**
     * Delete old signature file for a user.
     *
     * @param int $userId
     */
    public function deleteOldSignature(int $userId): void
    {
        $old = DB::table('users')->where('id_user', $userId)->value('signature_path');
        if ($old) {
            $oldPath = storage_path(self::SIGNATURE_STORAGE_PATH . '/' . $old);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }
    }

    /**
     * Download SPP attachment with authorization check.
     *
     * @param string $namaFile
     * @param string|null $role
     * @param string|null $userArea
     * @param string|null $userProject
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

        if (!$fileRecord) {
            return null;
        }

        if (!$this->canAccessFile($role, $userArea, $userProject, $fileRecord)) {
            return null;
        }

        $path = storage_path(self::SPP_STORAGE_PATH . '/' . $namaFile);
        if (!file_exists($path)) {
            return null;
        }

        return response()->download($path);
    }

    /**
     * Check if user can access a specific SPP file.
     *
     * @param string|null $role
     * @param string|null $userArea
     * @param string|null $userProject
     * @param object $fileRecord With kode_area, kode_project properties
     * @return bool
     */
    public function canAccessFile(?string $role, ?string $userArea, ?string $userProject, object $fileRecord): bool
    {
        $isGlobalRole = in_array($role, ['ADMIN', 'KASIR_PUSAT', 'DIREKTUR'], true);
        if ($isGlobalRole) {
            return true;
        }

        $isStaffArea = in_array($role, ['MAKER', 'AREA_MANAGER'], true) 
            && ($userArea === $fileRecord->kode_area);
        if ($isStaffArea) {
            return true;
        }

        if (in_array($role, ['FINANCE_PROJECT', 'PROJECT_MANAGER', 'MANAGER_KEUANGAN'], true)) {
            return ($role === 'MANAGER_KEUANGAN' && $userProject === null)
                || ($userProject === $fileRecord->kode_project);
        }

        return false;
    }

    /**
     * Delete a specific SPP file.
     *
     * @param string $namaFile
     * @return bool
     */
    public function deleteSppFile(string $namaFile): bool
    {
        $path = storage_path(self::SPP_STORAGE_PATH . '/' . $namaFile);
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
     * @param string $namaFile
     * @return string|null Null if not found
     */
    public function getSppFilePath(string $namaFile): ?string
    {
        $path = storage_path(self::SPP_STORAGE_PATH . '/' . $namaFile);
        return file_exists($path) ? $path : null;
    }

    /**
     * Validate file extension against allowed types.
     *
     * @param UploadedFile $file
     * @param array $allowedTypes
     * @return bool
     */
    public function isValidFileType(UploadedFile $file, array $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png']): bool
    {
        $ext = strtolower($file->getClientOriginalExtension());
        return in_array($ext, $allowedTypes, true);
    }

    /**
     * Validate file size (in KB).
     *
     * @param UploadedFile $file
     * @param int $maxSizeKb Default 5120 (5MB)
     * @return bool
     */
    public function isValidFileSize(UploadedFile $file, int $maxSizeKb = 5120): bool
    {
        return $file->getSize() <= ($maxSizeKb * 1024);
    }

    private function generateUniqueId(): string
    {
        return bin2hex(random_bytes(8));
    }
}
