<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\RestoresCivicContent;

class ProjectPolicy
{
    use RestoresCivicContent;

    public function viewAny(User $user): bool
    {
        return $this->canViewProjectContent($user);
    }

    public function view(User $user, Project $project): bool
    {
        return $this->canViewProjectContent($user);
    }

    public function create(User $user): bool
    {
        return $user->canManageProjects();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->canManageProjects();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }

    public function approve(User $user, Project $project): bool
    {
        return $user->canReviewPublicContent();
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
