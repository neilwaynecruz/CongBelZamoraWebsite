<?php

namespace App\Policies;

use App\Models\ServiceGuideStep;
use App\Models\User;
use App\Policies\Concerns\RestoresCivicContent;

class ServiceGuideStepPolicy
{
    use RestoresCivicContent;

    public function viewAny(User $user): bool
    {
        return $user->canManagePrograms() || $user->canReviewPublicContent() || $user->canAuditRecords();
    }

    public function view(User $user, ServiceGuideStep $serviceGuideStep): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->canManagePrograms();
    }

    public function update(User $user, ServiceGuideStep $serviceGuideStep): bool
    {
        return $user->canManagePrograms();
    }

    public function delete(User $user, ServiceGuideStep $serviceGuideStep): bool
    {
        return $user->canManagePrograms();
    }
}
