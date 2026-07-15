<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BudgetImportController extends Controller
{
    public function showForm()
    {
        // Reuse index page flash messages; keep simple view in a modal-style partial.
        return view('admin.budget.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        $rows = [];
        $errors = [];

        try {
            if ($ext === 'csv') {
                $rows = $this->parseCsv($file);
            } elseif (in_array($ext, ['xlsx', 'xls'], true)) {
                // Avoid adding a hard dependency (maatwebsite/excel) unless the project has it.
                // If dependency exists, we can support XLSX later. For now, show meaningful error.
                $errors[] = 'Import Excel (XLSX/XLS) belum diaktifkan karena package excel belum terpasang di project ini.';
                return back()->with('import_error', $errors)->withInput();
            } else {
                $errors[] = 'Format file tidak didukung.';
            }
        } catch (\Throwable $e) {
            $errors[] = 'Gagal membaca file: ' . $e->getMessage();
            return back()->with('import_error', $errors)->withInput();
        }

        // Expected headers (case-insensitive):
        // kode_project, kode_budget, nama_budget, alokasi_dana
        // nama_budget optional, but if present we will update it.

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $idx => $r) {
                $lineNo = $idx + 2; // +header row

                $kode_project = isset($r['kode_project']) ? trim((string) $r['kode_project']) : '';
                $kode_budget  = isset($r['kode_budget']) ? trim((string) $r['kode_budget']) : '';
                $nama_budget  = isset($r['nama_budget']) ? trim((string) $r['nama_budget']) : null;
                $alokasi_dana = isset($r['alokasi_dana']) ? $r['alokasi_dana'] : null;

                // Terkadang angka dari CSV dibaca sebagai string dengan spasi/typo
                // sehingga terlihat kosong. Tangani yang whitespace.
                if (is_string($alokasi_dana)) {
                    $alokasi_dana = trim($alokasi_dana);
                }
                if ($alokasi_dana !== null && $alokasi_dana === '') {
                    $alokasi_dana = null;
                }

                if ($kode_project === '' || $kode_budget === '' || $alokasi_dana === null || $alokasi_dana === '') {
                    $skipped++;
                    $errors[] = "Baris {$lineNo}: kode_project/kode_budget/alokasi_dana wajib diisi.";
                    continue;
                }

                if (!is_numeric($alokasi_dana)) {
                    $skipped++;
                    $errors[] = "Baris {$lineNo}: alokasi_dana harus angka.";
                    continue;
                }

                $alokasi_dana = $this->normalizeMoney($alokasi_dana);

                // Upsert based on (kode_project, kode_budget)
                $existing = MasterBudget::query()
                    ->where('kode_project', $kode_project)
                    ->where('kode_budget', $kode_budget)
                    ->first();

                if ($existing) {
                    // If name provided, update; else keep existing name.
                    $updatePayload = [
                        'alokasi_dana' => $alokasi_dana,
                    ];
                    if ($nama_budget !== null && $nama_budget !== '') {
                        $updatePayload['nama_budget'] = $nama_budget;
                    }

                    $old = $existing->toArray();
                    $existing->update($updatePayload);
                    $updated++;

                    self::simpanLog(
                        'MASTER_BUDGET_IMPORT_UPDATE',
                        "Update master_budget via import CSV (kode_project={$kode_project}, kode_budget={$kode_budget})",
                        $old
                    );
                } else {
                    MasterBudget::create([
                        'kode_project' => $kode_project,
                        'kode_budget' => $kode_budget,
                        'nama_budget' => $nama_budget ?? '',
                        'alokasi_dana' => $alokasi_dana,
                    ]);
                    $inserted++;

                    self::simpanLog(
                        'MASTER_BUDGET_IMPORT_INSERT',
                        "Insert master_budget via import CSV (kode_project={$kode_project}, kode_budget={$kode_budget})",
                        null
                    );
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[] = 'Gagal proses import: ' . $e->getMessage();
        }

        return back()->with([
            'import_success' => true,
            'import_summary' => [
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped,
            ],
            'import_error' => $errors,
        ]);
    }

    private function parseCsv($file): array
    {
        $path = $file->getRealPath();
        if (!$path) {
            throw new \RuntimeException('File tidak dapat diakses.');
        }

        $handle = fopen($path, 'rb');
        if (!$handle) {
            throw new \RuntimeException('Gagal membuka file CSV.');
        }

        $header = null;
        $rows = [];

        $separator = ',';

        // Try detect separator by reading first line
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return [];
        }
        $commaCount = substr_count($firstLine, ',');
        $semiCount = substr_count($firstLine, ';');
        if ($semiCount > $commaCount) {
            $separator = ';';
        }

        // Rewind to read headers with detected separator
        rewind($handle);

        while (($data = fgetcsv($handle, 0, $separator)) !== false) {
            if ($data === [null] || count($data) === 0) {
                continue;
            }

            // skip empty lines
            $allEmpty = true;
            foreach ($data as $v) {
                if (trim((string) $v) !== '') {
                    $allEmpty = false;
                    break;
                }
            }
            if ($allEmpty) {
                continue;
            }

            if ($header === null) {
                $header = array_map(function ($h) {
                    $h = strtolower(trim((string) $h));
                    // BOM/hidden chars
                    $h = preg_replace('/^[\x{FEFF}]+/u', '', $h);
                    $h = Str::of($h)->replace(['\u{FEFF}'], '');
                    return (string) $h;
                }, $data);

                continue;
            }

            // abaikan baris yang kemungkinan merupakan header ulang (mis. file ada baris kosong/terbaca sebagai baris data pertama)
            $isHeaderAgain = true;
            foreach ($header as $i => $key) {
                $cell = isset($data[$i]) ? strtolower(trim((string)$data[$i])) : '';
                if ($cell !== strtolower((string)$key)) {
                    $isHeaderAgain = false;
                    break;
                }
            }
            if ($isHeaderAgain) {
                continue;
            }

            $assoc = [];
            foreach ($header as $i => $key) {
                $assoc[$key] = isset($data[$i]) ? trim((string) $data[$i]) : '';
            }
            $rows[] = $assoc;
        }

        fclose($handle);

        // remove potential trailing header-like rows / blank rows already handled
        return $rows;
    }

    private function normalizeMoney($value): float
    {
        $v = is_string($value) ? trim($value) : $value;
        if (is_numeric($v)) {
            return (float) $v;
        }

        $v = str_replace(['Rp', ' ', '.'], ['', '', ''], (string) $v);
        $v = trim((string) $v);
        // handle comma decimal / thousand: we expect rupiah, so treat comma as thousand separator when no decimals.
        // Remove any non-digit except comma and minus.
        $v = preg_replace('/[^0-9,\-]/', '', (string) $v);

        // If contains both comma and dot, assume dot thousand already removed. If comma present, remove it.
        $v = str_replace(',', '', (string) $v);

        return (float) $v;
    }
}

