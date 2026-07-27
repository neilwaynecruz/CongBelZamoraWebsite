<?php

namespace App\Policies;

use App\Policies\Concerns\RestoresCivicContent;

use App\Enums\AdminRole;
use App\Models\ProjectEvidence;
use App\Models\User;

class ProjectEvidencePolicy
{
    use RestoresCivicContent;

    public function viewAny(User $user): bool
    {
        return $this->canViewProjectContent($user);
    }

    public function view(User $user, ProjectEvidence $projectEvidence): bool
    {
        return $this->canViewProjectContent($user);
    }

    public function create(User $user): bool
    {
        return $user->canManageProjects();
    }

    public function update(User $user, ProjectEvidence $projectEvidence): bool
    {
        return $user->canManageProjects();
    }

    public function delete(User $user, ProjectEvidence $projectEvidence): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
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
