<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\ServiceGuide;
use App\Models\User;

class ServiceGuidePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewServiceGuides($user);
    }

    public function view(User $user, ServiceGuide $serviceGuide): bool
    {
        return $this->canViewServiceGuides($user);
    }

    public function create(User $user): bool
    {
        return $user->canManagePrograms();
    }

    public function update(User $user, ServiceGuide $serviceGuide): bool
    {
        return $user->canManagePrograms();
    }

    public function delete(User $user, ServiceGuide $serviceGuide): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }

    public function approve(User $user, ServiceGuide $serviceGuide): bool
    {
        return $user->canReviewPublicContent();
    }

    private function canViewServiceGuides(User $user): bool
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
