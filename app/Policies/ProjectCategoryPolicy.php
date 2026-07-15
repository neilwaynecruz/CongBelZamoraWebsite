<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\ProjectCategory;
use App\Models\User;

class ProjectCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewProjectContent($user);
    }

    public function view(User $user, ProjectCategory $projectCategory): bool
    {
        return $this->canViewProjectContent($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }

    public function update(User $user, ProjectCategory $projectCategory): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, ProjectCategory $projectCategory): bool
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
