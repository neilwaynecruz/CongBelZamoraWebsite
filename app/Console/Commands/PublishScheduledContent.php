<?php

namespace App\Console\Commands;

use App\Enums\ContentApprovalStatus;
use App\Models\ContentApproval;
use App\Services\ContentPublicationService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class PublishScheduledContent extends Command
{
    protected $signature = 'content:publish-scheduled {--dry-run : Show due approvals without publishing}';

    protected $description = 'Publish scheduled approval records and promote their related content published_at gate.';

    public function handle(ContentPublicationService $publication): int
    {
        $approvals = ContentApproval::query()
            ->with('approvable')
            ->where('status', ContentApprovalStatus::Scheduled->value)
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<=', now())
            ->orderBy('scheduled_for')
            ->get();

        if ($approvals->isEmpty()) {
            $this->info('No scheduled approvals are due.');

            return self::SUCCESS;
        }

        foreach ($approvals as $approval) {
            /** @var ContentApproval $approval */
            $label = '#'.$approval->id.' '.($approval->approvable instanceof Model ? class_basename($approval->approvable).' #'.$approval->approvable->getKey() : 'unlinked approval');

            if ($this->option('dry-run')) {
                $this->line('Due: '.$label);

                continue;
            }

            $publication->publish($approval, event: 'content.scheduled_published');

            $this->info('Published: '.$label);
        }

        return self::SUCCESS;
    }
}
