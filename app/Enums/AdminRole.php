<?php

namespace App\Enums;

enum AdminRole: string
{
    case SuperAdministrator = 'super_administrator';
    case ContentAdministrator = 'content_administrator';
    case CommunicationsEditor = 'communications_editor';
    case ProgramOfficer = 'program_officer';
    case LegislativeEditor = 'legislative_editor';
    case ProjectEditor = 'project_editor';
    case MediaManager = 'media_manager';
    case ReviewerApprover = 'reviewer_approver';
    case InquiryOfficer = 'inquiry_officer';
    case ReadOnlyAuditor = 'read_only_auditor';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdministrator => 'Super Administrator',
            self::ContentAdministrator => 'Content Administrator',
            self::CommunicationsEditor => 'Communications Editor',
            self::ProgramOfficer => 'Program Officer',
            self::LegislativeEditor => 'Legislative Editor',
            self::ProjectEditor => 'Project Editor',
            self::MediaManager => 'Media Manager',
            self::ReviewerApprover => 'Reviewer / Approver',
            self::InquiryOfficer => 'Inquiry Officer',
            self::ReadOnlyAuditor => 'Read-Only Auditor',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role): array => [$role->value => $role->label()])
            ->all();
    }
}
