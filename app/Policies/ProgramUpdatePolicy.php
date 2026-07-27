<?php

namespace App\Policies;

use App\Policies\Concerns\RestoresCivicContent;

use App\Models\ProgramUpdate;
use App\Models\User;

class ProgramUpdatePolicy
{
    use RestoresCivicContent;

    public function viewAny(User $user): bool
    {
        return $user->canManagePrograms() || $user->canReviewPublicContent() || $user->canAuditRecords();
    }

    public function view(User $user, ProgramUpdate $programUpdate): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->canManagePrograms();
    }

    public function update(User $user, ProgramUpdate $programUpdate): bool
    {
        return $user->canManagePrograms();
    }

    public function delete(User $user, ProgramUpdate $programUpdate): bool
    {
        return $user->canManagePrograms();
    }
}
