<?php

namespace App\Policies\Concerns;

use App\Enums\AdminRole;
use App\Models\User;

trait RestoresCivicContent
{
    public function restore(User $user, mixed $record): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator);
    }

    public function forceDelete(User $user, mixed $record): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator);
    }
}
