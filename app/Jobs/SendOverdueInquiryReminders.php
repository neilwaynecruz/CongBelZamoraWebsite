<?php

namespace App\Jobs;

use App\Enums\AdminRole;
use App\Models\Inquiry;
use App\Models\User;
use App\Notifications\OverdueInquiryReminderNotification;
use App\Services\InquiryCaseManagementService;
use App\Support\ReliableQueuedWork;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class SendOverdueInquiryReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, ReliableQueuedWork, SerializesModels;

    public function __construct()
    {
        $this->configureReliableQueue();
    }

    public function handle(InquiryCaseManagementService $caseManagement): void
    {
        $graceHours = max(1, (int) config('belzamora.inquiries.case_management.overdue_reminder_grace_hours', 2));

        Inquiry::query()
            ->with('assignedOfficer')
            ->overdueFirstResponse()
            ->whereNull('overdue_reminder_sent_at')
            ->where('first_response_due_at', '<=', now()->subHours($graceHours))
            ->orderBy('first_response_due_at')
            ->chunkById(100, function (Collection $inquiries) use ($caseManagement): void {
                foreach ($inquiries as $inquiry) {
                    /** @var Inquiry $inquiry */
                    $recipients = $this->recipientsFor($inquiry);

                    if ($recipients->isEmpty()) {
                        continue;
                    }

                    Notification::send($recipients, new OverdueInquiryReminderNotification($inquiry));
                    $caseManagement->recordOverdueReminder($inquiry);
                }
            });
    }

    /**
     * @return Collection<int, User>
     */
    private function recipientsFor(Inquiry $inquiry): Collection
    {
        if ($inquiry->assignedOfficer instanceof User && $inquiry->assignedOfficer->is_active) {
            return collect([$inquiry->assignedOfficer]);
        }

        return User::query()
            ->where('is_active', true)
            ->whereIn('admin_role', [
                AdminRole::SuperAdministrator->value,
                AdminRole::InquiryOfficer->value,
            ])
            ->get();
    }
}
