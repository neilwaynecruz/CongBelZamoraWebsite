<?php

namespace App\Services;

use App\Enums\ProgramStatus;
use App\Enums\ResponsibleOfficeType;
use App\Models\Program;
use App\Models\ProgramRequirement;
use App\Models\ServiceGuide;
use App\Models\ServiceGuideStep;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PublicProgramReadService
{
    /**
     * @return array{
     *     breadcrumbs: array<int, array<string, string>>,
     *     programs: array<int, array<string, mixed>>,
     *     sectors: array<int, string>,
     *     statuses: array<int, string>,
     *     verifiedAt: string
     * }
     */
    public function indexProps(): array
    {
        /** @var array{breadcrumbs: array<int, array<string, string>>, programs: array<int, array<string, mixed>>, sectors: array<int, string>, statuses: array<int, string>, verifiedAt: string} $payload */
        $payload = Cache::remember(
            PublicCacheInvalidator::PROGRAMS_INDEX,
            (int) config('belzamora.cache.public_ttl_seconds', 60),
            function (): array {
                $programs = Program::query()
                    ->published()
                    ->with('sourceRecords')
                    ->ordered()
                    ->get()
                    ->map(fn (Program $program): array => $this->programCard($program));

                return [
                    'breadcrumbs' => [
                        ['title' => 'Home', 'href' => '/'],
                        ['title' => 'Programs'],
                    ],
                    'programs' => $programs->values()->all(),
                    'sectors' => $programs->pluck('sector')->unique()->values()->all(),
                    'statuses' => $programs->pluck('statusLabel')->unique()->values()->all(),
                    'verifiedAt' => $this->latestVerifiedAt($programs),
                ];
            },
        );

        return $payload;
    }

    /**
     * @return array{
     *     breadcrumbs: array<int, array<string, string>>,
     *     serviceOptions: array<int, array<string, mixed>>,
     *     verifiedAt: string
     * }
     */
    public function gabayProps(): array
    {
        /** @var array{breadcrumbs: array<int, array<string, string>>, serviceOptions: array<int, array<string, mixed>>, verifiedAt: string} $payload */
        $payload = Cache::remember(
            PublicCacheInvalidator::PROGRAMS_GABAY,
            (int) config('belzamora.cache.public_ttl_seconds', 60),
            function (): array {
                $serviceOptions = ServiceGuide::query()
                    ->published()
                    ->with('steps')
                    ->ordered()
                    ->get()
                    ->map(fn (ServiceGuide $serviceGuide): array => $this->serviceOption($serviceGuide));

                return [
                    'breadcrumbs' => [
                        ['title' => 'Home', 'href' => '/'],
                        ['title' => 'Programs', 'href' => '/programs'],
                        ['title' => 'Gabay San Juaneño'],
                    ],
                    'serviceOptions' => $serviceOptions->values()->all(),
                    'verifiedAt' => $this->latestVerifiedAt($serviceOptions),
                ];
            },
        );

        return $payload;
    }

    /**
     * @return array{
     *     breadcrumbs: array<int, array<string, string>>,
     *     program: array<string, mixed>
     * }
     */
    public function tulongDunongProps(): array
    {
        /** @var array{breadcrumbs: array<int, array<string, string>>, program: array<string, mixed>} $payload */
        $payload = Cache::remember(
            PublicCacheInvalidator::PROGRAMS_TULONG_DUNONG,
            (int) config('belzamora.cache.public_ttl_seconds', 60),
            function (): array {
                $program = Program::query()
                    ->published()
                    ->with(['requirements', 'sourceRecords'])
                    ->where('slug', 'ched-unifast-tulong-dunong')
                    ->firstOrFail();

                return [
                    'breadcrumbs' => [
                        ['title' => 'Home', 'href' => '/'],
                        ['title' => 'Programs', 'href' => '/programs'],
                        ['title' => 'Education'],
                        ['title' => 'Tulong Dunong'],
                    ],
                    'program' => $this->programDetail($program),
                ];
            },
        );

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function programCard(Program $program): array
    {
        return [
            'title' => $program->title,
            'href' => $program->href,
            'sector' => $program->sector,
            'status' => $this->programStatus($program)->value,
            'statusLabel' => $this->programStatus($program)->label(),
            'summary' => $program->summary,
            'responsibleOfficeType' => $this->responsibleOfficeType($program->responsible_office_type)->value,
            'responsibleOfficeLabel' => $program->responsible_office_label,
            'partnerAgency' => $program->partner_agency ?? '',
            'beneficiaryGroup' => $program->beneficiary_group ?? '',
            'applicationPeriod' => $program->application_period ?? '',
            'verifiedAt' => $this->formatDate($program->verified_at),
            'sourceLabel' => $program->source_label,
            'sourceUrl' => $program->source_url,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function programDetail(Program $program): array
    {
        $metadata = is_array($program->metadata) ? $program->metadata : [];
        $status = $this->programStatus($program);

        return [
            'title' => (string) ($metadata['detail_title'] ?? $program->title),
            'eyebrow' => (string) ($metadata['eyebrow'] ?? $program->sector),
            'description' => $program->description ?? $program->summary,
            'status' => $status->value,
            'statusLabel' => $status->label(),
            'responsibleOfficeLabel' => $program->responsible_office_label,
            'partnerAgencies' => $this->arrayOfStrings($metadata['partner_agencies'] ?? [$program->partner_agency]),
            'benefit' => $program->benefit ?? '',
            'applicationPeriod' => $program->application_period ?? '',
            'verifiedAt' => $this->formatDate($program->verified_at),
            'sourceLabel' => $program->source_label ?? 'Official source pending review',
            'sourceUrl' => $program->source_url ?? '#',
            'officeRole' => (string) ($metadata['office_role'] ?? ''),
            'eligibility' => $this->arrayOfStrings($metadata['eligibility'] ?? []),
            'requirementGroups' => $this->requirementGroups($program->requirements),
            'processSteps' => $this->arrayOfStrings($metadata['process_steps'] ?? []),
            'reminders' => $this->arrayOfStrings($metadata['reminders'] ?? []),
            'privacyNotes' => $this->arrayOfStrings($metadata['privacy_notes'] ?? []),
            'contactHref' => (string) ($metadata['contact_href'] ?? '/contact#inquiry-form'),
            'latestAnnouncementHref' => (string) ($metadata['latest_announcement_href'] ?? '/news'),
        ];
    }

    /**
     * @param  Collection<int, ProgramRequirement>  $requirements
     * @return array<int, array{title: string, items: array<int, string>, note?: string}>
     */
    private function requirementGroups(Collection $requirements): array
    {
        return $requirements
            ->groupBy('group_title')
            ->map(function (Collection $items, string $title): array {
                $first = $items->first();

                $group = [
                    'title' => $title,
                    'items' => $items->pluck('item_text')->values()->all(),
                ];

                if ($first instanceof ProgramRequirement && filled($first->note)) {
                    $group['note'] = $first->note;
                }

                return $group;
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceOption(ServiceGuide $serviceGuide): array
    {
        return [
            'id' => $serviceGuide->slug,
            'label' => $serviceGuide->label,
            'concern' => $serviceGuide->concern,
            'responsibleOfficeType' => $this->responsibleOfficeType($serviceGuide->responsible_office_type)->value,
            'responsibleOfficeLabel' => $serviceGuide->responsible_office_label,
            'districtOfficeRole' => $serviceGuide->district_office_role,
            'canDistrictOfficeDirectlyHandle' => $serviceGuide->can_district_office_directly_handle,
            'explanation' => $serviceGuide->explanation,
            'prepareItems' => $this->guideSteps($serviceGuide, 'prepare'),
            'doNotSubmitItems' => $this->guideSteps($serviceGuide, 'do_not_submit'),
            'nextStep' => $serviceGuide->next_step,
            'contactLabel' => $serviceGuide->contact_label,
            'contactHref' => $serviceGuide->contact_href,
            'verifiedAt' => $this->formatDate($serviceGuide->verified_at),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function guideSteps(ServiceGuide $serviceGuide, string $section): array
    {
        return $serviceGuide->steps
            ->where('section', $section)
            ->sortBy('sort_order')
            ->map(fn (ServiceGuideStep $step): string => $step->item_text)
            ->values()
            ->all();
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

    /**
     * @return array<int, string>
     */
    private function arrayOfStrings(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn (mixed $item): bool => is_string($item) && $item !== '')
            ->values()
            ->all();
    }

    private function programStatus(Program $program): ProgramStatus
    {
        return $program->status;
    }

    private function responsibleOfficeType(mixed $value): ResponsibleOfficeType
    {
        if ($value instanceof ResponsibleOfficeType) {
            return $value;
        }

        return ResponsibleOfficeType::from((string) $value);
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
