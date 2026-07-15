<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\Inquiry;
use App\Models\User;

class InquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAdminRole(
            AdminRole::SuperAdministrator,
            AdminRole::InquiryOfficer,
            AdminRole::ReadOnlyAuditor,
        );
    }

    public function view(User $user, Inquiry $inquiry): bool
    {
        if ($user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ReadOnlyAuditor)) {
            return true;
        }

        return $user->hasAdminRole(AdminRole::InquiryOfficer)
            && ($inquiry->assigned_officer_id === null || $inquiry->assigned_officer_id === $user->id);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Inquiry $inquiry): bool
    {
        if ($inquiry->anonymized_at !== null) {
            return false;
        }

        if ($user->hasAdminRole(AdminRole::SuperAdministrator)) {
            return true;
        }

        return $user->hasAdminRole(AdminRole::InquiryOfficer)
            && ($inquiry->assigned_officer_id === null || $inquiry->assigned_officer_id === $user->id);
    }

    public function delete(User $user, Inquiry $inquiry): bool
    {
        return false;
    }

    public function restore(User $user, Inquiry $inquiry): bool
    {
        return false;
    }

    public function forceDelete(User $user, Inquiry $inquiry): bool
    {
        return false;
    }
}
