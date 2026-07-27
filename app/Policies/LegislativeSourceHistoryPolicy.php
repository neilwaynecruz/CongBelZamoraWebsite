<?php

namespace App\Policies;

use App\Models\LegislativeSourceHistory;
use App\Models\User;
use App\Policies\Concerns\RestoresCivicContent;

class LegislativeSourceHistoryPolicy
{
    use RestoresCivicContent;

    public function viewAny(User $user): bool
    {
        return $user->canManageLegislation()
            || $user->canReviewPublicContent()
            || $user->canAuditRecords();
    }

    public function view(User $user, LegislativeSourceHistory $legislativeSourceHistory): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->canManageLegislation();
    }

    public function update(User $user, LegislativeSourceHistory $legislativeSourceHistory): bool
    {
        return $user->canManageLegislation();
    }

    public function delete(User $user, LegislativeSourceHistory $legislativeSourceHistory): bool
    {
        return $user->canManageLegislation();
    }
}
