<?php

namespace App\Enums;

enum ContentApprovalStatus: string
{
    case Draft = 'draft';
    case ForReview = 'for_review';
    case NeedsRevision = 'needs_revision';
    case Approved = 'approved';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::ForReview => 'For Review',
            self::NeedsRevision => 'Needs Revision',
            self::Approved => 'Approved',
            self::Scheduled => 'Scheduled',
            self::Published => 'Published',
            self::Archived => 'Archived',
            self::Rejected => 'Rejected',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}
