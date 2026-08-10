<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Models\AuditLog;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminConsoleLifecycleActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dossiers_expose_policy_safe_lifecycle_actions(): void
    {
        $administrator = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $superAdministrator = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $program = Program::factory()->create(['title' => 'Lifecycle Program']);

        $this->actingAs($administrator)
            ->get(route('admin-console.programs.show', $program))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Programs/Show')
                ->where('program.lifecycle.trashed', false)
                ->where('program.lifecycle.archiveUrl', route('admin-console.programs.archive', $program))
                ->where('program.can.archive', true)
                ->where('program.can.restore', false)
                ->where('program.can.forceDelete', false));

        $program->delete();

        $this->actingAs($superAdministrator)
            ->get(route('admin-console.programs.show', $program))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Programs/Show')
                ->where('program.lifecycle.trashed', true)
                ->where('program.lifecycle.restoreUrl', route('admin-console.programs.restore', $program))
                ->where('program.lifecycle.deleteUrl', route('admin-console.programs.delete', $program))
                ->where('program.can.restore', true)
                ->where('program.can.forceDelete', true));
    }

    public function test_content_administrator_can_archive_program_project_and_news_records(): void
    {
        $administrator = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $program = Program::factory()->create(['title' => 'Archive Ready Program']);
        $project = Project::factory()->create(['title' => 'Archive Ready Project']);
        $newsRecord = NewsRecord::factory()->create(['title' => 'Archive Ready News']);

        $this->actingAs($administrator)
            ->post(route('admin-console.programs.archive', $program), [
                'reason' => 'Replaced by a newer verified public guidance record.',
            ])
            ->assertRedirect();

        $this->actingAs($administrator)
            ->post(route('admin-console.projects.archive', $project), [
                'reason' => 'Project record is obsolete after source reconciliation.',
            ])
            ->assertRedirect();

        $this->actingAs($administrator)
            ->post(route('admin-console.news.archive', $newsRecord), [
                'reason' => 'News item was superseded by a verified current advisory.',
            ])
            ->assertRedirect();

        $this->assertSoftDeleted('programs', ['id' => $program->id]);
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
        $this->assertSoftDeleted('news_records', ['id' => $newsRecord->id]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $program->getMorphClass(),
            'auditable_id' => $program->id,
            'event' => 'program.archived',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $project->getMorphClass(),
            'auditable_id' => $project->id,
            'event' => 'project.archived',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $newsRecord->getMorphClass(),
            'auditable_id' => $newsRecord->id,
            'event' => 'news.record.archived',
        ]);
    }

    public function test_archive_requires_reason_and_policy_permission(): void
    {
        $programOfficer = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();
        $administrator = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $program = Program::factory()->create();

        $this->actingAs($administrator)
            ->post(route('admin-console.programs.archive', $program), ['reason' => 'short'])
            ->assertSessionHasErrors(['reason']);

        $this->actingAs($programOfficer)
            ->post(route('admin-console.programs.archive', $program), [
                'reason' => 'Program officer cannot archive this record directly.',
            ])
            ->assertForbidden();
    }

    public function test_super_administrator_can_restore_archived_records(): void
    {
        $superAdministrator = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $project = Project::factory()->create(['title' => 'Restorable Project']);

        $project->delete();

        $this->actingAs($superAdministrator)
            ->post(route('admin-console.projects.restore', $project), [
                'reason' => 'Restored after source reviewer confirmed it remains authoritative.',
            ])
            ->assertRedirect();

        $this->assertFalse($project->fresh()->trashed());
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $project->getMorphClass(),
            'auditable_id' => $project->id,
            'event' => 'project.restored',
        ]);
    }

    public function test_only_super_administrator_can_permanently_delete_with_exact_confirmation(): void
    {
        $administrator = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $superAdministrator = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $newsRecord = NewsRecord::factory()->create(['title' => 'Duplicate Staging News']);

        $newsRecord->delete();

        $this->actingAs($administrator)
            ->delete(route('admin-console.news.delete', $newsRecord), [
                'confirmation' => 'Duplicate Staging News',
                'reason' => 'Approved duplicate staging import removal after reconciliation.',
            ])
            ->assertForbidden();

        $this->actingAs($superAdministrator)
            ->delete(route('admin-console.news.delete', $newsRecord), [
                'confirmation' => 'Wrong title',
                'reason' => 'Approved duplicate staging import removal after reconciliation.',
            ])
            ->assertSessionHasErrors(['confirmation']);

        $this->actingAs($superAdministrator)
            ->delete(route('admin-console.news.delete', $newsRecord), [
                'confirmation' => 'Duplicate Staging News',
                'reason' => 'Approved duplicate staging import removal after reconciliation.',
            ])
            ->assertRedirect(route('admin-console.news.index'));

        $this->assertDatabaseMissing('news_records', ['id' => $newsRecord->id]);
        $this->assertTrue(
            AuditLog::query()
                ->where('event', 'news.record.permanently_deleted.confirmed')
                ->where('actor_user_id', $superAdministrator->id)
                ->exists(),
        );
    }
}
