<?php

namespace App\Policies;

use App\Policies\Concerns\RestoresCivicContent;

use App\Enums\AdminRole;
use App\Models\NewsRecord;
use App\Models\User;

class NewsRecordPolicy
{
    use RestoresCivicContent;

    public function viewAny(User $user): bool
    {
        return $this->canViewNewsContent($user);
    }

    public function view(User $user, NewsRecord $newsRecord): bool
    {
        return $this->canViewNewsContent($user);
    }

    public function create(User $user): bool
    {
        return $user->canManageNewsMedia();
    }

    public function update(User $user, NewsRecord $newsRecord): bool
    {
        return $user->canManageNewsMedia();
    }

    public function delete(User $user, NewsRecord $newsRecord): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }

    public function approve(User $user, NewsRecord $newsRecord): bool
    {
        return $user->canReviewPublicContent();
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
