<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\SourceRecord;
use App\Models\User;

class SourceRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, SourceRecord $sourceRecord): bool
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

    public function update(User $user, SourceRecord $sourceRecord): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, SourceRecord $sourceRecord): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }
}
