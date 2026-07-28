<?php

namespace App\Enums;

enum ContentSnapshotType: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';
    case Unpublished = 'unpublished';
    case Rollback = 'rollback';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft snapshot',
            self::Submitted => 'Submitted for review',
            self::Approved => 'Approved snapshot',
            self::Scheduled => 'Scheduled snapshot',
            self::Published => 'Published snapshot',
            self::Archived => 'Archived snapshot',
            self::Unpublished => 'Unpublished snapshot',
            self::Rollback => 'Rollback snapshot',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}
