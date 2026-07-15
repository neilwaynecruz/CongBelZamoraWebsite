<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\NewsStatusTone;
use App\Enums\NewsStatusValue;
use App\Filament\Resources\NewsMediaAssetResource;
use App\Filament\Resources\NewsRecordResource;
use App\Models\ContentApproval;
use App\Models\NewsCategory;
use App\Models\NewsMediaAsset;
use App\Models\NewsRecord;
use App\Models\NewsStatus;
use App\Models\SourceRecord;
use App\Models\User;
use Database\Seeders\NewsRecordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase7NewsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_schema_exists(): void
    {
        expect(Schema::hasTable('news_categories'))->toBeTrue()
            ->and(Schema::hasTable('news_statuses'))->toBeTrue()
            ->and(Schema::hasTable('news_records'))->toBeTrue()
            ->and(Schema::hasTable('news_media_assets'))->toBeTrue()
            ->and(Schema::hasColumn('news_records', 'stale_note'))->toBeTrue()
            ->and(Schema::hasColumn('news_records', 'review_due_at'))->toBeTrue()
            ->and(Schema::hasColumn('news_media_assets', 'rights_note'))->toBeTrue()
            ->and(Schema::hasColumn('news_media_assets', 'alt_text'))->toBeTrue();
    }

    public function test_news_models_cast_enums_and_keep_relationships(): void
    {
        $category = NewsCategory::factory()->create(['name' => 'Digital Government']);
        $status = NewsStatus::factory()->create([
            'value' => NewsStatusValue::Current,
            'label' => 'Current public-service update',
            'tone' => NewsStatusTone::Current,
        ]);
        $record = NewsRecord::factory()
            ->for($category, 'category')
            ->for($status, 'status')
            ->create();

        NewsMediaAsset::factory()
            ->for($record)
            ->create(['rights_note' => 'Approved local image copy required.']);

        expect($record->status->value)->toBe(NewsStatusValue::Current)
            ->and($record->status->tone)->toBe(NewsStatusTone::Current)
            ->and($record->category->is($category))->toBeTrue()
            ->and($record->mediaAssets)->toHaveCount(1);
    }

    public function test_seeded_news_has_media_source_and_approval_records(): void
    {
        $this->seed(NewsRecordSeeder::class);

        $record = NewsRecord::query()->where('slug', 'balik-sigla-bigay-saya-review')->firstOrFail();

        expect($record->status->value)->toBe(NewsStatusValue::StaleReview)
            ->and($record->status->tone)->toBe(NewsStatusTone::StaleReview)
            ->and($record->mediaAssets)->toHaveCount(1)
            ->and($record->sourceRecords)->toHaveCount(1)
            ->and($record->approvalRecords)->toHaveCount(1)
            ->and($record->approvalRecords->first()->status)->toBe(ContentApprovalStatus::Published)
            ->and(SourceRecord::query()->where('sourceable_type', NewsRecord::class)->count())->toBe(5)
            ->and(ContentApproval::query()->where('approvable_type', NewsRecord::class)->count())->toBe(5);
    }

    public function test_news_policies_are_least_privilege(): void
    {
        $communicationsEditor = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();
        $mediaManager = User::factory()->adminRole(AdminRole::MediaManager)->create();
        $contentAdmin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $projectEditor = User::factory()->adminRole(AdminRole::ProjectEditor)->create();
        $record = NewsRecord::factory()->create();
        $asset = NewsMediaAsset::factory()->for($record)->create();

        expect($communicationsEditor->can('update', $record))->toBeTrue()
            ->and($mediaManager->can('update', $asset))->toBeTrue()
            ->and($contentAdmin->can('delete', $record))->toBeTrue()
            ->and($reviewer->can('view', $record))->toBeTrue()
            ->and($reviewer->can('approve', $record))->toBeTrue()
            ->and($reviewer->can('update', $record))->toBeFalse()
            ->and($auditor->can('view', $record))->toBeTrue()
            ->and($auditor->can('update', $record))->toBeFalse()
            ->and($inquiryOfficer->can('view', $record))->toBeFalse()
            ->and($projectEditor->can('update', $record))->toBeFalse();
    }

    public function test_filament_news_resources_are_role_gated(): void
    {
        $communicationsEditor = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();
        $mediaManager = User::factory()->adminRole(AdminRole::MediaManager)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();

        $this->actingAs($communicationsEditor)
            ->get(NewsRecordResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($mediaManager)
            ->get(NewsMediaAssetResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($inquiryOfficer)
            ->get(NewsRecordResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_public_news_route_uses_backend_records_with_phase_6_contract(): void
    {
        $this->seed(NewsRecordSeeder::class);

        $this->get(route('public.news'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/News/Index')
                ->has('newsItems', 5)
                ->where('newsItems.0.slug', 'tulong-dunong-orientation')
                ->where('newsItems.0.status', NewsStatusValue::Current->value)
                ->where('newsItems.0.statusLabel', 'Current advisory')
                ->where('newsItems.4.statusLabel', 'Needs review before reuse')
                ->where('newsItems.4.statusTone', NewsStatusTone::StaleReview->value)
                ->has('categoryOptions')
                ->has('statusOptions')
                ->has('yearOptions')
                ->where('initialFilters.status', 'All statuses')
                ->where('verifiedAt', 'July 12, 2026'));

        $this->get(route('public.news', ['status' => 'stale-review']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/News/Index')
                ->where('initialFilters.status', 'Needs review before reuse'));
    }
}
