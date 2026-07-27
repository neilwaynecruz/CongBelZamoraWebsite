<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\InquiryCategory;
use App\Models\User;

class InquiryCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, InquiryCategory $inquiryCategory): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator, AdminRole::ContentAdministrator);
    }

    public function update(User $user, InquiryCategory $inquiryCategory): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, InquiryCategory $inquiryCategory): bool
    {
        return $user->hasAdminRole(AdminRole::SuperAdministrator);
    }
}
