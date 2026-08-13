<?php

namespace App\Http\Resources\AdminConsole;

use App\Models\SourceRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin SourceRecord
 */
class SourceRecordResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'href' => route('admin-console.source-records.show', $this->resource),
            'editHref' => route('admin-console.source-records.edit', $this->resource),
            'sourceType' => [
                'value' => $this->source_type->value,
                'label' => $this->source_type->label(),
            ],
            'sourceTier' => $this->source_tier,
            'publicStatus' => $this->public_status,
            'description' => $this->description,
            'owner' => $this->contentOwner?->name,
            'verifier' => $this->verifier?->name,
            'review' => [
                'verifiedAt' => $this->formatDate($this->verified_at),
                'lastReviewedAt' => $this->formatDate($this->last_reviewed_at),
                'nextReviewAt' => $this->formatDate($this->next_review_at),
                'effectiveAt' => $this->formatDate($this->effective_at),
                'expiresAt' => $this->formatDate($this->expires_at),
                'state' => $this->reviewState(),
            ],
            'sourceable' => [
                'label' => $this->relatedRecordLabel(),
                'type' => $this->sourceable instanceof Model ? class_basename($this->sourceable) : null,
                'id' => $this->sourceable instanceof Model ? $this->sourceable->getKey() : null,
            ],
            'metadata' => $this->metadata ?? [],
            'internalNotes' => $this->internal_notes,
            'lifecycle' => [
                'trashed' => $this->trashed(),
                'archiveUrl' => route('admin-console.source-records.archive', $this->resource),
                'restoreUrl' => route('admin-console.source-records.restore', $this->resource),
            ],
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'archive' => $request->user()?->can('delete', $this->resource) ?? false,
                'restore' => $request->user()?->can('restore', $this->resource) ?? false,
            ],
        ];
    }

    /**
     * @return array{value: string, label: string, tone: string}
     */
    private function reviewState(): array
    {
        if ($this->trashed()) {
            return ['value' => 'archived', 'label' => 'Archived', 'tone' => 'neutral'];
        }

        if ($this->expires_at instanceof \DateTimeInterface && $this->expires_at <= now()) {
            return ['value' => 'expired', 'label' => 'Expired', 'tone' => 'critical'];
        }

        if ($this->next_review_at instanceof \DateTimeInterface && $this->next_review_at <= now()) {
            return ['value' => 'due', 'label' => 'Review due', 'tone' => 'warning'];
        }

        if (! $this->verified_at instanceof \DateTimeInterface) {
            return ['value' => 'unverified', 'label' => 'Verification missing', 'tone' => 'critical'];
        }

        return ['value' => 'current', 'label' => 'Current', 'tone' => 'healthy'];
    }

    private function relatedRecordLabel(): string
    {
        if (! $this->sourceable instanceof Model) {
            return 'Global / unattached source';
        }

        foreach (['title', 'public_title', 'official_title', 'label', 'name', 'reference_code', 'bill_number'] as $attribute) {
            $value = $this->sourceable->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return str(class_basename($this->sourceable))->headline().': '.$value;
            }
        }

        return str(class_basename($this->sourceable))->headline().' #'.$this->sourceable->getKey();
    }

    private function formatDate(mixed $value): ?string
    {
        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone('Asia/Manila')->format('M j, Y')
            : null;
    }
}
