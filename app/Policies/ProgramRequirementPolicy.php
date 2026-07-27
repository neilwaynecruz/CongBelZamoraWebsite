<?php

namespace App\Policies;

use App\Policies\Concerns\RestoresCivicContent;

use App\Models\ProgramRequirement;
use App\Models\User;

class ProgramRequirementPolicy
{
    use RestoresCivicContent;

    public function viewAny(User $user): bool
    {
        return $user->canManagePrograms() || $user->canReviewPublicContent() || $user->canAuditRecords();
    }

    public function view(User $user, ProgramRequirement $programRequirement): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->canManagePrograms();
    }

    public function update(User $user, ProgramRequirement $programRequirement): bool
    {
        return $user->canManagePrograms();
    }

    public function delete(User $user, ProgramRequirement $programRequirement): bool
    {
        return $user->canManagePrograms();
    }
}
