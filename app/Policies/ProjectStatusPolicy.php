<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\ProjectStatus;
use App\Models\User;

class ProjectStatusPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewProjectContent($user);
    }

    public function view(User $user, ProjectStatus $projectStatus): bool
    {
        return $this->canViewProjectContent($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }

    public function update(User $user, ProjectStatus $projectStatus): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, ProjectStatus $projectStatus): bool
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
