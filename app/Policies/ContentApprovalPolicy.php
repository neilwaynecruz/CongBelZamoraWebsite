<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\ContentApproval;
use App\Models\User;

class ContentApprovalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, ContentApproval $contentApproval): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::ContentAdministrator,
            AdminRole::CommunicationsEditor,
            AdminRole::ProgramOfficer,
            AdminRole::LegislativeEditor,
            AdminRole::ProjectEditor,
            AdminRole::MediaManager,
        );
    }

    public function update(User $user, ContentApproval $contentApproval): bool
    {
        return $user->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::ContentAdministrator,
            AdminRole::ReviewerApprover,
        );
    }

    public function delete(User $user, ContentApproval $contentApproval): bool
    {
        return false;
    }
}
