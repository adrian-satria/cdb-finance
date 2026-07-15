<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

class BudgetValidationService
{
    /**
     * Validate if budget ceiling can accommodate the requested amount.
     *
     * @param string $kodeBudget Budget code to validate
     * @param string $jumlah Amount requested (string for bcmath precision)
     * @return ValidationResult Validation result
     */
    public function validateBudgetCeiling(string $kodeBudget, string $jumlah): ValidationResult
    {
        try {
            $budget = DB::table('master_budget')
                ->where('kode_budget', $kodeBudget)
                ->lockForUpdate()
                ->first();

            if (!$budget) {
                return $this->failure(
                    "Kode Budget [{$kodeBudget}] tidak ditemukan di sistem master!",
                    ['kode_budget' => $kodeBudget]
                );
            }

            $sisaSaldo = bcsub((string)$budget->alokasi_dana, (string)$budget->terserap, 2);

            if (bccomp($jumlah, $sisaSaldo, 2) === 1) {
                $namaBudget = $budget->nama_budget ?? $kodeBudget;
                $sisaSaldoFormat = number_format($sisaSaldo, 0, ',', '.');
                $dimintaFormat = number_format($jumlah, 0, ',', '.');

                return $this->failure(
                    "⚠️ PENGAKSESAN DANA DITOLAK! Saldo untuk akun [{$namaBudget}] tidak mencukupi. Sisa saldo saat ini: Rp {$sisaSaldoFormat}, namun Anda mencoba mengajukan: Rp {$dimintaFormat}.",
                    [
                        'kode_budget' => $kodeBudget,
                        'nama_budget' => $namaBudget,
                        'alokasi' => $budget->alokasi_dana,
                        'terserap' => $budget->terserap,
                        'sisa_saldo' => $sisaSaldo,
                        'diminta' => $jumlah,
                    ]
                );
            }

            return $this->success([
                'kode_budget' => $kodeBudget,
                'sisa_saldo' => $sisaSaldo,
                'diminta' => $jumlah,
            ]);

        } catch (Exception $e) {
            return $this->failure(
                "Error validating budget: " . $e->getMessage(),
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Validate multiple budget items at once.
     *
     * @param array $items Array of items with 'kode_budget' and 'jumlah'
     * @return ValidationResult Validation result
     */
    public function lockAndValidateMultiple(array $items): ValidationResult
    {
        DB::beginTransaction();

        try {
            $totalNominal = '0';

            foreach ($items as $item) {
                $kodeBudget = $item['kode_budget'];
                $jumlah = (string)$item['jumlah'];

                $validation = $this->validateBudgetCeiling($kodeBudget, $jumlah);

                if (!$validation->isValid) {
                    DB::rollBack();
                    return $validation;
                }

                $totalNominal = bcadd($totalNominal, $jumlah, 2);
            }

            DB::commit();

            return $this->success([
                'total_items' => count($items),
                'total_nominal' => $totalNominal,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->failure(
                "Error validating multiple items: " . $e->getMessage(),
                ['exception' => $e->getMessage()]
            );
        }
    }

    public function calculateRemainingBudget(string $kodeBudget): string
    {
        $budget = DB::table('master_budget')
            ->where('kode_budget', $kodeBudget)
            ->first();

        if (!$budget) {
            return '0';
        }

        return bcsub((string)$budget->alokasi_dana, (string)$budget->terserap, 2);
    }

    public function budgetExists(string $kodeBudget): bool
    {
        return DB::table('master_budget')
            ->where('kode_budget', $kodeBudget)
            ->exists();
    }

    public function getBudgetDetails(string $kodeBudget): ?object
    {
        return DB::table('master_budget')
            ->where('kode_budget', $kodeBudget)
            ->first();
    }

    public function isWithinBudget(string $kodeBudget, string $jumlah): bool
    {
        $remaining = $this->calculateRemainingBudget($kodeBudget);
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

class ValidationResult
{
    public function __construct(
        public bool $isValid,
        public ?string $errorMessage = null,
        public ?array $details = null,
    ) {}
}
