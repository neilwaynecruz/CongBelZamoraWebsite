<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\LegislativeRecord;
use App\Models\User;
use App\Policies\Concerns\RestoresCivicContent;

class LegislativeRecordPolicy
{
    use RestoresCivicContent;

    public function viewAny(User $user): bool
    {
        return $this->canViewLegislation($user);
    }

    public function view(User $user, LegislativeRecord $legislativeRecord): bool
    {
        return $this->canViewLegislation($user);
    }

    public function create(User $user): bool
    {
        return $user->canManageLegislation();
    }

    public function update(User $user, LegislativeRecord $legislativeRecord): bool
    {
        return $user->canManageLegislation();
    }

    public function delete(User $user, LegislativeRecord $legislativeRecord): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }

    public function approve(User $user, LegislativeRecord $legislativeRecord): bool
    {
        return $user->canReviewPublicContent();
    }

    private function canViewLegislation(User $user): bool
    {
        return $user->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::ContentAdministrator,
            AdminRole::LegislativeEditor,
            AdminRole::ReviewerApprover,
            AdminRole::ReadOnlyAuditor,
        );
    }
}
