<?php

namespace App\Http\Resources\AdminConsole;

use App\Models\NewsMediaAsset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin NewsMediaAsset
 */
class ManagedNewsMediaAssetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'mediaType' => $this->media_type,
            'mediaPath' => $this->media_path,
            'previewUrl' => $this->media_path ? Storage::disk('public')->url($this->media_path) : null,
            'sourceLabel' => $this->source_label,
            'sourceUrl' => $this->source_url,
            'caption' => $this->caption,
            'altText' => $this->alt_text,
            'rightsNote' => $this->rights_note,
            'verifiedAt' => $this->verified_at?->toDateString(),
            'verifiedDisplay' => $this->verified_at?->timezone('Asia/Manila')->format('M j, Y'),
            'verifier' => $this->verifier?->name,
            'sortOrder' => $this->sort_order,
            'storage' => [
                'disk' => (string) data_get($this->metadata, 'storage_disk', 'public'),
                'visibility' => (string) data_get($this->metadata, 'visibility', 'public'),
            ],
            'rightsReady' => filled($this->media_path)
                && filled($this->caption)
                && filled($this->alt_text)
                && filled($this->rights_note)
                && $this->verified_at !== null,
            'urls' => [
                'update' => route('admin-console.news.media.update', [$this->news_record_id, $this->resource]),
                'delete' => route('admin-console.news.media.delete', [$this->news_record_id, $this->resource]),
            ],
            'can' => [
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
            ],
        ];
    }
}
