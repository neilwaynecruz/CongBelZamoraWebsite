<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\ContentApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ContentApprovalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, ContentApproval $contentApproval): bool
    {
        return $user->isStaff() && $this->canAccessRelatedContent($user, $contentApproval);
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
        return $this->hasWorkflowRole($user, 'review')
            && $this->canAccessRelatedContent($user, $contentApproval);
    }

    public function assignReviewer(User $user, ContentApproval $contentApproval): bool
    {
        return $this->hasWorkflowRole($user, 'review')
            && $this->canAccessRelatedContent($user, $contentApproval);
    }

    public function submit(User $user, ContentApproval $contentApproval): bool
    {
        return $this->hasWorkflowRole($user, 'draft')
            && $this->canAccessRelatedContent($user, $contentApproval);
    }

    public function approve(User $user, ContentApproval $contentApproval): bool
    {
        return $this->hasWorkflowRole($user, 'approve')
            && $this->canAccessRelatedContent($user, $contentApproval);
    }

    public function requestChanges(User $user, ContentApproval $contentApproval): bool
    {
        return $this->hasWorkflowRole($user, 'review')
            && $this->canAccessRelatedContent($user, $contentApproval);
    }

    public function reject(User $user, ContentApproval $contentApproval): bool
    {
        return $this->hasWorkflowRole($user, 'review')
            && $this->canAccessRelatedContent($user, $contentApproval);
    }

    public function cancel(User $user, ContentApproval $contentApproval): bool
    {
        $canWithdrawOwnSubmission = $contentApproval->submitted_by_id === $user->id
            && $this->hasWorkflowRole($user, 'draft');

        return ($canWithdrawOwnSubmission || $this->hasWorkflowRole($user, 'schedule'))
            && $this->canAccessRelatedContent($user, $contentApproval);
    }

    public function schedule(User $user, ContentApproval $contentApproval): bool
    {
        return $this->hasWorkflowRole($user, 'schedule')
            && $this->canAccessRelatedContent($user, $contentApproval);
    }

    public function publish(User $user, ContentApproval $contentApproval): bool
    {
        return $this->hasWorkflowRole($user, 'publish')
            && $this->canAccessRelatedContent($user, $contentApproval);
    }

    public function unpublish(User $user, ContentApproval $contentApproval): bool
    {
        return $this->hasWorkflowRole($user, 'archive')
            && $this->canAccessRelatedContent($user, $contentApproval);
    }

    public function archive(User $user, ContentApproval $contentApproval): bool
    {
        return $this->hasWorkflowRole($user, 'archive')
            && $this->canAccessRelatedContent($user, $contentApproval);
    }

    public function rollback(User $user, ContentApproval $contentApproval): bool
    {
        return $this->hasWorkflowRole($user, 'archive')
            && $this->canAccessRelatedContent($user, $contentApproval);
    }

    public function delete(User $user, ContentApproval $contentApproval): bool
    {
        return false;
    }

    private function canAccessRelatedContent(User $user, ContentApproval $contentApproval): bool
    {
        $contentApproval->loadMissing('approvable');

        return $contentApproval->approvable instanceof Model
            && $user->can('view', $contentApproval->approvable);
    }

    private function hasWorkflowRole(User $user, string $action): bool
    {
        $roles = config("belzamora.governance.workflow_roles.{$action}", []);

        return $user->admin_role instanceof AdminRole
            && is_array($roles)
            && in_array($user->admin_role->value, $roles, true);
    }
}
