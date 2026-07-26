<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Models\Program;
use App\Models\PublicPage;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class LaunchApprovalChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $submitter = User::query()->firstOrCreate(
            ['email' => 'content-launch@example.com'],
            [
                'name' => 'Launch Content Coordinator',
                'password' => bcrypt('password'),
                'admin_role' => AdminRole::ContentAdministrator,
                'email_verified_at' => now(),
            ],
        );

        $reviewer = User::query()->firstOrCreate(
            ['email' => 'district-approver@example.com'],
            [
                'name' => 'District Office Approver',
                'password' => bcrypt('password'),
                'admin_role' => AdminRole::ReviewerApprover,
                'email_verified_at' => now(),
            ],
        );

        $this->createLaunchGate(
            record: PublicPage::query()->where('slug', 'home')->firstOrFail(),
            revision: 2,
            submitter: $submitter,
            reviewer: $reviewer,
            launchItem: 'Homepage and official identity wording',
            checklist: [
                'Confirm official District Office name and public-service positioning.',
                'Confirm Cong. Bel logo, House seal, and San Juan seal usage for the public header.',
                'Confirm public hero copy does not imply services, benefits, or legal outcomes beyond approved wording.',
            ],
        );

        $this->createLaunchGate(
            record: PublicPage::query()->where('slug', 'contact')->firstOrFail(),
            revision: 2,
            submitter: $submitter,
            reviewer: $reviewer,
            launchItem: 'Office contact, hours, and response-time policy',
            checklist: [
                'Confirm office name, address, phone, email, and any public-facing office hours.',
                'Confirm the inquiry response-time policy or keep the page as "no fixed public SLA published".',
                'Confirm privacy reminder wording for basic inquiries and no document uploads.',
            ],
        );

        $this->createLaunchGate(
            record: Program::query()->where('slug', 'ched-unifast-tulong-dunong')->firstOrFail(),
            revision: 2,
            submitter: $submitter,
            reviewer: $reviewer,
            launchItem: 'Tulong Dunong dates, requirements, and benefit wording',
            checklist: [
                'Confirm current application period, deadlines, slots, and any first-come-first-served rule before publishing exact claims.',
                'Confirm accepted requirements for the current academic year and remove outdated document lists.',
                'Confirm benefit amount and eligibility language with CHED / UniFAST and District Office guidance.',
            ],
        );

        $this->createLaunchGate(
            record: PublicPage::query()->where('slug', 'about')->firstOrFail(),
            revision: 2,
            submitter: $submitter,
            reviewer: $reviewer,
            launchItem: 'Official profile and source-link review',
            checklist: [
                'Confirm public biography facts against official House and District Office records.',
                'Confirm committee/source links are current or clearly marked as official source references.',
                'Confirm no unsupported claims are shown on public-facing profile and service pages.',
            ],
        );
    }

    /**
     * @param  list<string>  $checklist
     */
    private function createLaunchGate(
        Model $record,
        int $revision,
        User $submitter,
        User $reviewer,
        string $launchItem,
        array $checklist,
    ): void {
        $metadata = [
            'launch_gate' => true,
            'launch_item' => $launchItem,
            'checklist' => $checklist,
            'required_reviewer_role' => $reviewer->admin_role?->value,
            'approval_instruction' => 'Mark Approved only after the District Office confirms every checklist item.',
        ];

        $approval = $record->approvalRecords()->firstOrNew(
            ['revision_number' => $revision],
        );

        if (! $approval->exists) {
            $approval->fill([
                'status' => ContentApprovalStatus::ForReview,
                'submitted_by_id' => $submitter->id,
                'submitted_at' => now(),
                'review_notes' => 'Launch approval gate: District Office review required before production release.',
                'rejection_reason' => null,
                'is_fast_track' => false,
            ]);
        }

        $approval->metadata = array_merge($approval->metadata ?? [], $metadata);
        $approval->save();
    }
}
