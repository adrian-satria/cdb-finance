<?php

namespace App\Services;

use App\ValueObjects\ValidationResult;
use Exception;
use Illuminate\Support\Facades\DB;

class BudgetValidationService
{
    /**
     * Validate multiple budget items against budget_area (batched).
     * For no_budget_projects, skip validation entirely.
     * Allows overspend: returns isValid=true with isOverBudget flag.
     *
     * @param  array  $items  Array of items with 'kode_budget' and 'jumlah'
     * @param  string|null  $kodeArea  Area code for per-area budget check
     * @param  string|null  $kodeProject  Project code (for no-budget exclusion)
     * @return ValidationResult
     */
    public function lockAndValidateMultiple(array $items, ?string $kodeArea = null, ?string $kodeProject = null): ValidationResult
    {
        try {
            if ($kodeProject && in_array($kodeProject, config('spp_workflow.no_budget_projects', []), true)) {
                $nominal = array_reduce($items, fn($c, $i) => bcadd($c, (string) ($i['jumlah'] ?? '0'), 2), '0');
                return $this->success([
                    'total_nominal' => $nominal,
                    'no_budget' => true,
                ]);
            }

            $kodeArea = $kodeArea ?? 'PUSAT';
            $kodeBudgets = array_map(fn($i) => $i['kode_budget'], $items);
            sort($kodeBudgets, SORT_STRING);

            $budgets = DB::table('budget_area')
                ->whereIn('kode_budget', $kodeBudgets)
                ->where('kode_area', $kodeArea)
                ->lockForUpdate()
                ->get()
                ->keyBy('kode_budget');

            $totalNominal = '0';
            $isOverBudget = false;
            $overbudgetItems = [];
            $totalDefisit = '0';

            foreach ($items as $item) {
                $kodeBudget = $item['kode_budget'];
                $jumlah = (string) $item['jumlah'];
                $budget = $budgets->get($kodeBudget);

                if (! $budget) {
                    return $this->failure(
                        "Kode Budget [{$kodeBudget}] tidak ditemukan di area {$kodeArea}!",
                        ['kode_budget' => $kodeBudget, 'kode_area' => $kodeArea]
                    );
                }

                $sisaSaldo = bcsub(
                    bcsub((string) $budget->alokasi_dana, (string) $budget->terserap, 2),
                    (string) ($budget->terserap_sementara ?? 0),
                    2
                );

                if (bccomp($jumlah, $sisaSaldo, 2) === 1) {
                    $isOverBudget = true;
                    $defisit = bcsub($sisaSaldo, $jumlah, 2);
                    $totalDefisit = bcadd($totalDefisit, $defisit, 2);
                    $overbudgetItems[] = [
                        'kode_budget' => $kodeBudget,
                        'nama_budget' => $budget->nama_budget ?? $kodeBudget,
                        'alokasi' => $budget->alokasi_dana,
                        'terserap' => $budget->terserap,
                        'sisa_saldo' => $sisaSaldo,
                        'diminta' => $jumlah,
                        'defisit' => $defisit,
                    ];
                }

                $totalNominal = bcadd($totalNominal, $jumlah, 2);
            }

            $result = $this->success([
                'total_items' => count($items),
                'total_nominal' => $totalNominal,
                'is_overbudget' => $isOverBudget,
                'overbudget_items' => $overbudgetItems,
                'total_defisit' => $totalDefisit,
                'kode_area' => $kodeArea,
            ]);

            if ($isOverBudget) {
                $result->isOverBudget = true;
                $result->defisit = $totalDefisit;
                $result->overbudgetItems = $overbudgetItems;
            }

            return $result;

        } catch (Exception $e) {
            return $this->failure(
                'Error validating budget: '.$e->getMessage(),
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Update budget_area.terserap and sync master_budget.terserap.
     * For no_budget_projects, skip entirely.
     *
     * @param  array  $items  Items with 'kode_budget' and 'nominal'
     * @param  string  $kodeArea  Area code
     * @param  string  $kodeProject  Project code
     * @param  string  $biayaAdmin  Additional admin fee
     */
    public function updateTerserap(array $items, string $kodeArea, string $kodeProject, string $biayaAdmin = '0'): void
    {
        if (in_array($kodeProject, config('spp_workflow.no_budget_projects', []), true)) {
            return;
        }

        DB::transaction(function () use ($items, $kodeArea, $biayaAdmin) {
            $budgetCodes = array_map(fn($i) => $i['kode_budget'], $items);
            sort($budgetCodes, SORT_STRING);

            $budgets = DB::table('budget_area')
                ->whereIn('kode_budget', $budgetCodes)
                ->where('kode_area', $kodeArea)
                ->lockForUpdate()
                ->get()
                ->keyBy('kode_budget');

            $adminFeeApplied = false;

            foreach ($items as $item) {
                $budget = $budgets->get($item['kode_budget']);
                if (!$budget) continue;

                $nominal = (string) ($item['nominal'] ?? $item['jumlah'] ?? '0');
                $total = $nominal;

                if (!$adminFeeApplied && bccomp($biayaAdmin, '0', 2) === 1) {
                    $total = bcadd($nominal, $biayaAdmin, 2);
                    $adminFeeApplied = true;
                }

                DB::table('budget_area')
                    ->where('id', $budget->id)
                    ->update([
                        'terserap' => DB::raw("terserap + {$total}"),
                        'terserap_sementara' => DB::raw("GREATEST(0, terserap_sementara - {$nominal})"),
                    ]);
            }

            // Sync master_budget.terserap = SUM(budget_area.terserap)
            foreach ($budgetCodes as $code) {
                $sum = DB::table('budget_area')
                    ->where('kode_budget', $code)
                    ->sum('terserap');
                DB::table('master_budget')
                    ->where('kode_budget', $code)
                    ->update(['terserap' => $sum]);
            }
        });
    }

    /**
     * Reserve budget for a new SPP (terserap_sementara) to prevent double-booking.
     * Called inside the store transaction after SPP record is created.
     */
    public function reserveBudget(array $items, string $kodeArea, string $kodeProject): void
    {
        if (in_array($kodeProject, config('spp_workflow.no_budget_projects', []), true)) {
            return;
        }

        foreach ($items as $item) {
            DB::table('budget_area')
                ->where('kode_budget', $item['kode_budget'])
                ->where('kode_area', $kodeArea)
                ->increment('terserap_sementara', (string) ($item['jumlah'] ?? $item['nominal'] ?? '0'));
        }
    }

    /**
     * Release budget reservation for a rejected SPP.
     */
    public function releaseReservation(array $items, string $kodeArea, string $kodeProject): void
    {
        if (in_array($kodeProject, config('spp_workflow.no_budget_projects', []), true)) {
            return;
        }

        foreach ($items as $item) {
            DB::table('budget_area')
                ->where('kode_budget', $item['kode_budget'])
                ->where('kode_area', $kodeArea)
                ->decrement('terserap_sementara', (string) ($item['nominal'] ?? $item['jumlah'] ?? '0'));
        }
    }

    public function calculateRemainingBudget(string $kodeBudget, ?string $kodeArea = null): string
    {
        $q = DB::table('budget_area')->where('kode_budget', $kodeBudget);
        if ($kodeArea) $q->where('kode_area', $kodeArea);
        $budget = $q->first();

        if (!$budget) return '0';
        return bcsub(
            bcsub((string) $budget->alokasi_dana, (string) $budget->terserap, 2),
            (string) ($budget->terserap_sementara ?? 0),
            2
        );
    }

    public function budgetExists(string $kodeBudget, ?string $kodeArea = null): bool
    {
        $q = DB::table('budget_area')->where('kode_budget', $kodeBudget);
        if ($kodeArea) $q->where('kode_area', $kodeArea);
        return $q->exists();
    }

    public function getBudgetDetails(string $kodeBudget, ?string $kodeArea = null): ?object
    {
        $q = DB::table('budget_area')->where('kode_budget', $kodeBudget);
        if ($kodeArea) $q->where('kode_area', $kodeArea);
        return $q->first();
    }

    public function isWithinBudget(string $kodeBudget, string $jumlah, ?string $kodeArea = null): bool
    {
        $remaining = $this->calculateRemainingBudget($kodeBudget, $kodeArea);
        return bccomp($jumlah, $remaining, 2) !== 1;
    }

    private function success(array $details = []): ValidationResult
    {
        return new ValidationResult(true, null, $details);
    }

    private function failure(string $message, array $details = []): ValidationResult
    {
        return new ValidationResult(false, $message, $details);
    }
}
