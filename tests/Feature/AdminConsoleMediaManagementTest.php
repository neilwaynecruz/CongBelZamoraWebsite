<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Models\NewsMediaAsset;
use App\Models\NewsRecord;
use App\Models\Project;
use App\Models\ProjectEvidence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminConsoleMediaManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_media_manager_is_policy_protected_and_exposes_upload_policy(): void
    {
        $mediaManager = User::factory()->adminRole(AdminRole::MediaManager)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $record = NewsRecord::factory()->create(['title' => 'Rights Ready Update']);
        NewsMediaAsset::factory()->for($record)->create(['title' => 'Existing public image']);

        $this->actingAs($mediaManager)
            ->get(route('admin-console.news.media', $record))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Media/NewsMedia')
                ->where('newsRecord.title', 'Rights Ready Update')
                ->where('mediaPolicy.disk', 'public')
                ->where('mediaPolicy.visibility', 'public')
                ->has('mediaAssets', 1));

        $this->actingAs($inquiryOfficer)
            ->get(route('admin-console.news.media', $record))
            ->assertForbidden();
    }

    public function test_news_media_upload_validates_files_and_persists_with_audit_log(): void
    {
        Storage::fake('public');

        $staff = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();
        $record = NewsRecord::factory()->create();

        $this->actingAs($staff)
            ->post(route('admin-console.news.media.store', $record), [
                'title' => 'Unsupported attachment',
                'media_type' => 'image',
                'media_file' => UploadedFile::fake()->create('private-case.pdf', 24, 'application/pdf'),
                'source_label' => 'District Office media desk',
                'source_url' => 'https://example.gov.ph/media',
                'caption' => 'Official public caption.',
                'alt_text' => 'Residents attending an official district office activity.',
                'rights_note' => 'Approved for public website use.',
                'verified_at' => now()->toDateString(),
            ])
            ->assertSessionHasErrors('media_file');

        $this->actingAs($staff)
            ->post(route('admin-console.news.media.store', $record), $this->validUploadPayload())
            ->assertRedirect(route('admin-console.news.media', $record));

        $asset = NewsMediaAsset::query()->where('news_record_id', $record->id)->firstOrFail();

        Storage::disk('public')->assertExists($asset->media_path);
        $this->assertStringStartsWith('approved-media/news/', (string) $asset->media_path);
        $this->assertSame($staff->id, $asset->verified_by_id);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $asset->getMorphClass(),
            'auditable_id' => $asset->id,
            'event' => 'news_media_asset.created',
        ]);
    }

    public function test_news_media_update_replaces_public_file_and_removal_deletes_file(): void
    {
        Storage::fake('public');

        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $record = NewsRecord::factory()->create();
        Storage::disk('public')->put('approved-media/news/old.jpg', 'old');
        $asset = NewsMediaAsset::factory()->for($record)->create([
            'media_path' => 'approved-media/news/old.jpg',
            'verified_by_id' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->post(route('admin-console.news.media.update', [$record, $asset]), array_merge(
                $this->validUploadPayload('Updated official image'),
                ['_method' => 'put'],
            ))
            ->assertRedirect(route('admin-console.news.media', $record));

        $asset->refresh();

        Storage::disk('public')->assertMissing('approved-media/news/old.jpg');
        Storage::disk('public')->assertExists($asset->media_path);
        $this->assertSame('Updated official image', $asset->title);

        $this->actingAs($staff)
            ->delete(route('admin-console.news.media.delete', [$record, $asset]), [
                'confirmation' => 'remove media asset',
            ])
            ->assertRedirect(route('admin-console.news.media', $record));

        Storage::disk('public')->assertMissing($asset->media_path);
        $this->assertSoftDeleted('news_media_assets', ['id' => $asset->id]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $asset->getMorphClass(),
            'auditable_id' => $asset->id,
            'event' => 'news_media_asset.removed',
        ]);
    }

    public function test_project_evidence_upload_update_and_delete_follow_project_policies(): void
    {
        Storage::fake('public');

        $projectEditor = User::factory()->adminRole(AdminRole::ProjectEditor)->create();
        $contentAdmin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $project = Project::factory()->create(['title' => 'Evidence Managed Project']);

        $this->actingAs($projectEditor)
            ->get(route('admin-console.projects.evidence', $project))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Media/ProjectEvidence')
                ->where('project.title', 'Evidence Managed Project')
                ->where('can.create', true));

        $this->actingAs($projectEditor)
            ->post(route('admin-console.projects.evidence.store', $project), $this->validUploadPayload('Launch evidence'))
            ->assertRedirect(route('admin-console.projects.evidence', $project));

        $evidence = ProjectEvidence::query()->where('project_id', $project->id)->firstOrFail();

        Storage::disk('public')->assertExists($evidence->media_path);
        $this->assertStringStartsWith('approved-media/project-evidence/', (string) $evidence->media_path);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $evidence->getMorphClass(),
            'auditable_id' => $evidence->id,
            'event' => 'project_evidence.created',
        ]);

        $this->actingAs($projectEditor)
            ->delete(route('admin-console.projects.evidence.delete', [$project, $evidence]), [
                'confirmation' => 'remove evidence record',
            ])
            ->assertForbidden();

        $this->actingAs($contentAdmin)
            ->delete(route('admin-console.projects.evidence.delete', [$project, $evidence]), [
                'confirmation' => 'remove evidence record',
            ])
            ->assertRedirect(route('admin-console.projects.evidence', $project));

        Storage::disk('public')->assertMissing($evidence->media_path);
        $this->assertSoftDeleted('project_evidences', ['id' => $evidence->id]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validUploadPayload(string $title = 'Approved public image'): array
    {
        return [
            'title' => $title,
            'media_type' => 'image',
            'media_file' => UploadedFile::fake()->image('approved-public-image.jpg', 1200, 800),
            'source_label' => 'District Office media desk',
            'source_url' => 'https://example.gov.ph/media',
            'caption' => 'Official public caption approved for website publication.',
            'alt_text' => 'Residents attending an official district office public-service activity.',
            'rights_note' => 'Rights-cleared by the District Office communications team for public website use.',
            'verified_at' => now()->toDateString(),
            'sort_order' => 1,
        ];
    }
}
