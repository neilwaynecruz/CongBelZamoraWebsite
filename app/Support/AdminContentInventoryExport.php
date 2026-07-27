<?php

namespace App\Support;

use App\Models\LegislativeRecord;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\PublicPage;
use App\Models\ServiceGuide;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminContentInventoryExport
{
    public function stream(): StreamedResponse
    {
        $filename = 'content-inventory-'.now('Asia/Manila')->format('Ymd-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['type', 'title', 'slug_or_reference', 'owner', 'approver', 'published_at', 'review_due_at', 'source_label', 'source_url']);

            $this->writeRows($handle, PublicPage::query()->with(['contentOwner', 'approver'])->orderBy('slug')->get(), 'Public page', 'slug', 'title', 'source_label', 'source_url');
            $this->writeRows($handle, Program::query()->with(['contentOwner', 'approver'])->orderBy('title')->get(), 'Program', 'slug', 'title', 'source_label', 'source_url');
            $this->writeRows($handle, ServiceGuide::query()->with(['contentOwner', 'approver'])->orderBy('label')->get(), 'Service guide', 'slug', 'title', null, null);
            $this->writeRows($handle, LegislativeRecord::query()->with(['contentOwner', 'approver'])->orderBy('bill_number')->get(), 'Legislative record', 'bill_number', 'public_title', 'official_source_label', 'official_source_url');
            $this->writeRows($handle, Project::query()->with(['contentOwner', 'approver'])->orderBy('title')->get(), 'Project', 'slug', 'title', 'source_label', 'source_url');
            $this->writeRows($handle, NewsRecord::query()->with(['contentOwner', 'approver'])->orderByDesc('published_at')->get(), 'News record', 'slug', 'title', 'source_label', 'source_url');

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  resource  $handle
     * @param  Collection<int, object>  $records
     */
    private function writeRows($handle, Collection $records, string $type, string $referenceField, string $titleField, ?string $sourceLabelField, ?string $sourceUrlField): void
    {
        foreach ($records as $record) {
            fputcsv($handle, [
                $type,
                data_get($record, $titleField),
                data_get($record, $referenceField),
                data_get($record, 'contentOwner.name'),
                data_get($record, 'approver.name'),
                data_get($record, 'published_at')?->timezone('Asia/Manila')->toDateTimeString(),
                data_get($record, 'review_due_at')?->timezone('Asia/Manila')->toDateTimeString(),
                $sourceLabelField !== null ? data_get($record, $sourceLabelField) : '',
                $sourceUrlField !== null ? data_get($record, $sourceUrlField) : '',
            ]);
        }
    }
}
