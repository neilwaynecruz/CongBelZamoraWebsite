<?php

namespace App\Policies;

use App\Policies\Concerns\RestoresCivicContent;

use App\Enums\AdminRole;
use App\Models\PublicPage;
use App\Models\User;

class PublicPagePolicy
{
    use RestoresCivicContent;

    public function viewAny(User $user): bool
    {
        return $this->canViewPublicPages($user);
    }

    public function view(User $user, PublicPage $publicPage): bool
    {
        return $this->canViewPublicPages($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::ContentAdministrator,
            AdminRole::CommunicationsEditor,
        );
    }

    public function update(User $user, PublicPage $publicPage): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, PublicPage $publicPage): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }

    public function approve(User $user, PublicPage $publicPage): bool
    {
        return $user->canReviewPublicContent();
    }

    private function canViewPublicPages(User $user): bool
    {
        return $user->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::ContentAdministrator,
            AdminRole::CommunicationsEditor,
            AdminRole::ReviewerApprover,
            AdminRole::ReadOnlyAuditor,
        );
    }
}
