<?php

namespace Tests\Feature;

use App\Enums\ContentApprovalStatus;
use App\Enums\ContentSnapshotType;
use App\Models\AuditLog;
use App\Models\ContentApproval;
use App\Models\ContentSnapshot;
use App\Models\Program;
use App\Models\PublicPage;
use App\Services\ContentGovernanceService;
use App\Services\ContentPublicationService;
use App\Services\ContentSnapshotService;
use App\Support\ContentGovernanceException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase2ContentGovernanceVersioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_2_schema_adds_snapshots_and_editorial_metadata(): void
    {
        $this->assertTrue(Schema::hasTable('content_snapshots'));

        foreach ([
            'content_approval_id',
            'approvable_type',
            'approvable_id',
            'version_number',
            'snapshot_type',
            'change_summary',
            'payload',
            'payload_hash',
            'diff_from_previous',
            'is_publishable',
            'governance_issues',
            'captured_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('content_snapshots', $column), "Missing content_snapshots.{$column}");
        }

        foreach ([
            'change_summary',
            'archive_reason',
            'valid_until',
            'unpublished_at',
            'rollback_snapshot_id',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('content_approvals', $column), "Missing content_approvals.{$column}");
        }
    }

    public function test_publish_captures_normalized_published_snapshot(): void
    {
        $page = PublicPage::factory()->create(['published_at' => null]);
        $approval = ContentApproval::factory()
            ->for($page, 'approvable')
            ->create([
                'status' => ContentApprovalStatus::Approved,
                'change_summary' => 'Approved verified Contact page wording.',
            ]);

        app(ContentPublicationService::class)->publish($approval);

        $snapshot = ContentSnapshot::query()->first();

        $this->assertNotNull($page->fresh()?->published_at);
        $this->assertInstanceOf(ContentSnapshot::class, $snapshot);
        $this->assertSame(ContentSnapshotType::Published, $snapshot->snapshot_type);
        $this->assertSame($page->id, $snapshot->approvable_id);
        $this->assertSame(PublicPage::class, $snapshot->approvable_type);
        $this->assertTrue($snapshot->is_publishable);
        $this->assertSame($page->title, data_get($snapshot->payload, 'attributes.title'));
        $this->assertDatabaseHas('audit_logs', ['event' => 'content.snapshot_captured']);
    }

    public function test_publish_gate_blocks_incomplete_public_records(): void
    {
        $program = Program::factory()->unpublished()->create([
            'source_url' => null,
            'verified_at' => null,
            'content_owner_id' => null,
        ]);
        $approval = ContentApproval::factory()
            ->for($program, 'approvable')
            ->create([
                'status' => ContentApprovalStatus::Approved,
                'change_summary' => 'Attempt to publish incomplete program.',
            ]);

        try {
            app(ContentPublicationService::class)->publish($approval);
            $this->fail('Expected governance exception was not thrown.');
        } catch (ContentGovernanceException $exception) {
            $issues = implode(' ', $exception->issues());

            $this->assertStringContainsString('source_url', $issues);
            $this->assertStringContainsString('verified_at', $issues);
            $this->assertStringContainsString('content_owner_id', $issues);
        }

        $this->assertNull($program->fresh()?->published_at);
        $this->assertSame(0, ContentSnapshot::query()->count());
    }

    public function test_snapshot_compare_and_rollback_restore_previous_published_content(): void
    {
        $page = PublicPage::factory()->create([
            'title' => 'Published About Page',
            'published_at' => null,
        ]);
        $approval = ContentApproval::factory()
            ->for($page, 'approvable')
            ->create([
                'status' => ContentApprovalStatus::Approved,
                'change_summary' => 'Initial approved public page.',
            ]);

        app(ContentPublicationService::class)->publish($approval);
        $publishedSnapshot = ContentSnapshot::query()
            ->where('snapshot_type', ContentSnapshotType::Published)
            ->firstOrFail();

        $page->forceFill(['title' => 'Draft About Page Change'])->save();

        $diff = app(ContentSnapshotService::class)->compareCurrentToPublished($approval->fresh(['approvable']) ?? $approval);

        $this->assertArrayHasKey('title', $diff);
        $this->assertSame('Published About Page', $diff['title']['from']);
        $this->assertSame('Draft About Page Change', $diff['title']['to']);

        app(ContentPublicationService::class)->rollback(
            $approval->fresh(['approvable']) ?? $approval,
            $publishedSnapshot,
            reason: 'Restore last published wording after editorial review.',
        );

        $this->assertSame('Published About Page', $page->fresh()?->title);
        $this->assertSame(ContentSnapshotType::Rollback, ContentSnapshot::query()->latest('version_number')->first()?->snapshot_type);
        $this->assertDatabaseHas('audit_logs', ['event' => 'content.rollback']);
    }

    public function test_archive_unpublishes_public_content_and_records_reasoned_snapshot(): void
    {
        $page = PublicPage::factory()->create(['published_at' => now()]);
        $approval = ContentApproval::factory()
            ->for($page, 'approvable')
            ->create([
                'status' => ContentApprovalStatus::Published,
                'published_at' => now(),
                'change_summary' => 'Published page ready for archive test.',
            ]);

        app(ContentPublicationService::class)->archive($approval, 'Superseded by newer approved advisory.');

        $approval->refresh();

        $this->assertNull($page->fresh()?->published_at);
        $this->assertSame(ContentApprovalStatus::Archived, $approval->status);
        $this->assertNotNull($approval->archived_at);
        $this->assertNotNull($approval->unpublished_at);
        $this->assertSame('Superseded by newer approved advisory.', $approval->archive_reason);
        $this->assertSame(ContentSnapshotType::Archived, ContentSnapshot::query()->first()?->snapshot_type);
        $this->assertSame(1, AuditLog::query()->where('event', 'content.archived')->count());
    }

    public function test_change_summary_is_required_before_publish(): void
    {
        $page = PublicPage::factory()->create(['published_at' => null]);
        $approval = ContentApproval::factory()
            ->for($page, 'approvable')
            ->create([
                'status' => ContentApprovalStatus::Approved,
                'change_summary' => null,
            ]);

        $issues = app(ContentGovernanceService::class)->blockingIssues($approval);

        $this->assertContains('A clear change summary is required before schedule or publish.', $issues);
    }
}
