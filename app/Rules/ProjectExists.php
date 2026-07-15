<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ProjectExists implements Rule
{
    public function passes($attribute, $value): bool
    {
        return DB::table('project')
            ->where('kode_project', $value)
            ->exists();
    }

    public function message(): string
    {
        return 'Project dengan kode :input tidak ditemukan.';
    }
}
