<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\NewsMediaAsset;
use App\Models\User;

class NewsMediaAssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewNewsContent($user);
    }

    public function view(User $user, NewsMediaAsset $newsMediaAsset): bool
    {
        return $this->canViewNewsContent($user);
    }

    public function create(User $user): bool
    {
        return $user->canManageNewsMedia();
    }

    public function update(User $user, NewsMediaAsset $newsMediaAsset): bool
    {
        return $user->canManageNewsMedia();
    }

    public function delete(User $user, NewsMediaAsset $newsMediaAsset): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
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
