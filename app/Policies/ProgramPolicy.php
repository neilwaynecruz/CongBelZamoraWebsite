<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewProgramContent($user);
    }

    public function view(User $user, Program $program): bool
    {
        return $this->canViewProgramContent($user);
    }

    public function create(User $user): bool
    {
        return $user->canManagePrograms();
    }

    public function update(User $user, Program $program): bool
    {
        return $user->canManagePrograms();
    }

    public function delete(User $user, Program $program): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }

    public function approve(User $user, Program $program): bool
    {
        return $user->canReviewPublicContent();
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
