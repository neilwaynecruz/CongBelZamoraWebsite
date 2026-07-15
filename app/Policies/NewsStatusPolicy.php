<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\NewsStatus;
use App\Models\User;

class NewsStatusPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewNewsContent($user);
    }

    public function view(User $user, NewsStatus $newsStatus): bool
    {
        return $this->canViewNewsContent($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }

    public function update(User $user, NewsStatus $newsStatus): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, NewsStatus $newsStatus): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator);
    }

    private function canViewNewsContent(User $user): bool
    {
        return $user->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::ContentAdministrator,
            AdminRole::CommunicationsEditor,
            AdminRole::MediaManager,
            AdminRole::ReviewerApprover,
            AdminRole::ReadOnlyAuditor,
        );
    }
}
