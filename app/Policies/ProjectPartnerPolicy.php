<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\ProjectPartner;
use App\Models\User;

class ProjectPartnerPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewProjectContent($user);
    }

    public function view(User $user, ProjectPartner $projectPartner): bool
    {
        return $this->canViewProjectContent($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }

    public function update(User $user, ProjectPartner $projectPartner): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, ProjectPartner $projectPartner): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator);
    }

    private function canViewProjectContent(User $user): bool
    {
        return $user->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::ContentAdministrator,
            AdminRole::ProjectEditor,
            AdminRole::ReviewerApprover,
            AdminRole::ReadOnlyAuditor,
        );
    }
}
