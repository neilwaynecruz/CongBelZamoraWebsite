<?php

namespace App\Http\Requests\AdminConsole\Media;

use App\Models\NewsMediaAsset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveNewsMediaAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $asset = $this->route('newsMediaAsset');

        if ($asset instanceof NewsMediaAsset) {
            return $this->user()?->can('update', $asset) ?? false;
        }

        return $this->user()?->can('create', NewsMediaAsset::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $asset = $this->route('newsMediaAsset');
        $requiresFile = ! ($asset instanceof NewsMediaAsset) || blank($asset->media_path);

        return [
            'title' => ['required', 'string', 'max:255'],
            'media_type' => ['required', Rule::in(['image'])],
            'media_file' => [
                $requiresFile ? 'required' : 'nullable',
                'file',
                'mimetypes:'.implode(',', config('belzamora.media.allowed_mimetypes', [])),
                'max:'.(int) config('belzamora.media.max_upload_kilobytes', 5120),
            ],
            'remove_file' => ['nullable', 'boolean'],
            'source_label' => ['required', 'string', 'max:255'],
            'source_url' => ['required', 'url', 'max:500'],
            'caption' => ['required', 'string', 'max:2000'],
            'alt_text' => ['required', 'string', 'max:2000'],
            'rights_note' => ['required', 'string', 'max:2000'],
            'verified_at' => ['nullable', 'date', 'before_or_equal:today'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $validated = $this->validated();
        $validated['media_type'] = 'image';
        $validated['remove_file'] = (bool) ($validated['remove_file'] ?? false);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        return $validated;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'media_file.required' => 'Upload an approved public image before saving this media asset.',
            'media_file.mimetypes' => 'Use only approved image formats: JPG, PNG, or WebP.',
            'rights_note.required' => 'Record the media-rights basis before publishing or review.',
        ];
    }
}
