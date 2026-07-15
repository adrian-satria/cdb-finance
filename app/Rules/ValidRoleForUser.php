<?php

namespace App\Rules;

use App\Http\Controllers\Admin\UserAccessController;
use Illuminate\Contracts\Validation\Rule;

class ValidRoleForUser implements Rule
{
    public function passes($attribute, $value): bool
    {
        return in_array($value, UserAccessController::VALID_ROLES, true);
    }

    public function message(): string
    {
        return 'Role :input tidak valid.';
    }
}
