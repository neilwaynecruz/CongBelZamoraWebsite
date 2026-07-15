<?php

namespace App\Console\Commands;

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use App\Services\AuditLogService;
use Illuminate\Console\Command;

class AnonymizeExpiredInquiries extends Command
{
    protected $signature = 'inquiries:anonymize-expired {--dry-run : Count records without updating them}';

    protected $description = 'Anonymize citizen inquiry personal data after the approved retention period.';

    public function handle(AuditLogService $auditLog): int
    {
        $query = Inquiry::query()->expiredForRetention();
        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("{$count} expired inquiry record(s) would be anonymized.");

            return self::SUCCESS;
        }

        $query->chunkById(100, function ($inquiries) use ($auditLog): void {
            foreach ($inquiries as $inquiry) {
                /** @var Inquiry $inquiry */
                $inquiry->forceFill([
                    'citizen_name' => 'Anonymized citizen',
                    'citizen_email' => null,
                    'citizen_mobile' => null,
                    'message_body' => 'Anonymized after retention period.',
                    'status' => InquiryStatus::Anonymized,
                    'assigned_officer_id' => null,
                    'routing_notes' => null,
                    'anonymized_at' => now(),
                ])->save();

                $auditLog->record(
                    event: 'inquiry.anonymized',
                    auditable: $inquiry,
                    newValues: [
                        'reference_code' => $inquiry->reference_code,
                        'status' => InquiryStatus::Anonymized->value,
                        'anonymized_at' => (string) $inquiry->anonymized_at,
                    ],
                    metadata: [
                        'retention_expires_at' => (string) $inquiry->retention_expires_at,
                        'pii_values_redacted' => true,
                    ],
                );
            }
        });

        $this->info("Anonymized {$count} expired inquiry record(s).");

        return self::SUCCESS;
    }
}
