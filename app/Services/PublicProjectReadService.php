<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublicProjectReadService
{
    /**
     * @return array{
     *     breadcrumbs: array<int, array<string, string>>,
     *     projects: array<int, array<string, mixed>>,
     *     barangayOptions: array<int, string>,
     *     statusOptions: array<int, string>,
     *     categoryOptions: array<int, string>,
     *     initialFilters: array{barangay: string, status: string, category: string},
     *     verifiedAt: string
     * }
     */
    public function indexProps(Request $request): array
    {
        $items = Project::query()
            ->published()
            ->with(['category', 'status', 'primaryPartner', 'evidences'])
            ->ordered()
            ->get()
            ->map(fn (Project $project): array => $this->projectItem($project));

        return [
            'breadcrumbs' => [
                ['title' => 'Home', 'href' => '/'],
                ['title' => 'Projects'],
            ],
            'projects' => $items->values()->all(),
            'barangayOptions' => $items->pluck('barangay')->unique()->values()->all(),
            'statusOptions' => $items->pluck('statusLabel')->unique()->values()->all(),
            'categoryOptions' => $items->pluck('category')->unique()->values()->all(),
            'initialFilters' => [
                'barangay' => $request->query('barangay', 'All barangays'),
                'status' => $this->statusFromQuery($request),
                'category' => $request->query('category', 'All categories'),
            ],
            'verifiedAt' => $this->latestVerifiedAt($items),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function projectItem(Project $project): array
    {
        return [
            'title' => $project->title,
            'slug' => $project->slug,
            'category' => $project->category->name,
            'status' => $project->status->value->value,
            'statusLabel' => $project->status->label,
            'statusTone' => $project->status->tone->value,
            'barangay' => $project->barangay,
            'location' => $project->location,
            'responsibleOffice' => $project->responsible_office,
            'partner' => $project->partner_label,
            'summary' => $project->summary,
            'evidenceSummary' => $project->evidence_summary,
            'impactNote' => $project->impact_note,
            'imageGuidance' => $project->image_guidance,
            'sourceLabel' => $project->source_label,
            'sourceUrl' => $project->source_url,
            'verifiedAt' => $this->formatDate($project->verified_at),
        ];
    }

    private function statusFromQuery(Request $request): string
    {
        return match ($request->query('status')) {
            'completed' => 'Completed / documented',
            'ongoing' => 'Active / ongoing service',
            'source-pending' => 'Source review pending',
            default => 'All statuses',
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     */
    private function latestVerifiedAt(Collection $items): string
    {
        $latest = $items
            ->pluck('verifiedAt')
            ->filter()
            ->sort()
            ->last();

        return is_string($latest) ? $latest : 'Source review pending';
    }

    private function formatDate(mixed $date): string
    {
        if (! $date instanceof \DateTimeInterface) {
            return 'Source review pending';
        }

        return \DateTimeImmutable::createFromInterface($date)
            ->setTimezone(new \DateTimeZone('Asia/Manila'))
            ->format('F j, Y');
    }
}
