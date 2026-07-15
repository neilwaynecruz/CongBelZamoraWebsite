<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\ProgramCategory;
use App\Models\User;

class ProgramCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewProgramContent($user);
    }

    public function view(User $user, ProgramCategory $programCategory): bool
    {
        return $this->canViewProgramContent($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }

    public function update(User $user, ProgramCategory $programCategory): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, ProgramCategory $programCategory): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator);
    }

    private function canViewProgramContent(User $user): bool
    {
        return $user->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::ContentAdministrator,
            AdminRole::ProgramOfficer,
            AdminRole::ReviewerApprover,
            AdminRole::ReadOnlyAuditor,
        );
    }
}
