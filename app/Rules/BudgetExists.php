<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class BudgetExists implements Rule
{
    public function passes($attribute, $value): bool
    {
        return DB::table('master_budget')
            ->where('kode_budget', $value)
            ->exists();
    }

    public function message(): string
    {
        return 'Kode budget :input tidak ditemukan di master budget.';
    }
}
