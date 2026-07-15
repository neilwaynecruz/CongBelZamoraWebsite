<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\NewsCategory;
use App\Models\User;

class NewsCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewNewsContent($user);
    }

    public function view(User $user, NewsCategory $newsCategory): bool
    {
        return $this->canViewNewsContent($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }

    public function update(User $user, NewsCategory $newsCategory): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, NewsCategory $newsCategory): bool
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
