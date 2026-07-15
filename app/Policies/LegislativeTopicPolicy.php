<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\LegislativeTopic;
use App\Models\User;

class LegislativeTopicPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewLegislationTaxonomy($user);
    }

    public function view(User $user, LegislativeTopic $legislativeTopic): bool
    {
        return $this->canViewLegislationTaxonomy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }

    public function update(User $user, LegislativeTopic $legislativeTopic): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, LegislativeTopic $legislativeTopic): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator);
    }

    private function canViewLegislationTaxonomy(User $user): bool
    {
        return $user->canManageLegislation()
            || $user->canReviewPublicContent()
            || $user->canAuditRecords();
    }
}
