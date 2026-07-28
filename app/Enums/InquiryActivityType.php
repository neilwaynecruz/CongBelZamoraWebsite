<?php

namespace App\Enums;

enum InquiryActivityType: string
{
    case Submitted = 'submitted';
    case Routed = 'routed';
    case Assigned = 'assigned';
    case StatusChanged = 'status_changed';
    case InternalNote = 'internal_note';
    case StaffResponse = 'staff_response';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Reopened = 'reopened';
    case OverdueReminder = 'overdue_reminder';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Routed => 'Routed',
            self::Assigned => 'Assigned',
            self::StatusChanged => 'Status changed',
            self::InternalNote => 'Internal note',
            self::StaffResponse => 'Staff response',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Reopened => 'Reopened',
            self::OverdueReminder => 'Overdue reminder',
        };
    }
}
