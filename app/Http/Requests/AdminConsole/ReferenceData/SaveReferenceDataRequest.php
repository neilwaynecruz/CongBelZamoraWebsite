<?php

namespace App\Http\Requests\AdminConsole\ReferenceData;

use App\Enums\LegislativeRoleValue;
use App\Enums\LegislativeStatusTone;
use App\Enums\NewsStatusTone;
use App\Enums\NewsStatusValue;
use App\Enums\ProjectStatusTone;
use App\Enums\ProjectStatusValue;
use App\Enums\SourceType;
use App\Services\AdminReferenceDataRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveReferenceDataRequest extends FormRequest
{
    public function authorize(AdminReferenceDataRegistry $registry): bool
    {
        $config = $registry->get((string) $this->route('type'));
        $routeRecord = $this->route('record');
        $record = $this->record($config);

        if ($routeRecord !== null && $record === null) {
            return false;
        }

        return $record !== null
            ? $this->user()?->can('update', $record) ?? false
            : $this->user()?->can('create', $config['model']) ?? false;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(AdminReferenceDataRegistry $registry): array
    {
        $config = $registry->get((string) $this->route('type'));
        $record = $this->record($config);
        $id = $record?->getKey();
        $table = (new $config['model'])->getTable();
        $fields = $config['fields'];
        $rules = [];

        if (in_array('name', $fields, true)) {
            $rules['name'] = ['required', 'string', 'max:160'];
        }

        if (in_array('slug', $fields, true)) {
            $rules['slug'] = [
                'required',
                'string',
                'max:160',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique($table, 'slug')->ignore($id),
            ];
        }

        if (in_array('code', $fields, true)) {
            $rules['code'] = [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique($table, 'code')->ignore($id),
            ];
        }

        if (in_array('label', $fields, true)) {
            $rules['label'] = ['required', 'string', 'max:180'];
        }

        if (in_array('description', $fields, true)) {
            $rules['description'] = ['nullable', 'string', 'max:1200'];
        }

        if (in_array('notes', $fields, true)) {
            $rules['notes'] = ['nullable', 'string', 'max:1200'];
        }

        if (in_array('office_type', $fields, true)) {
            $rules['office_type'] = ['nullable', 'string', 'max:120'];
        }

        if (in_array('website_url', $fields, true)) {
            $rules['website_url'] = ['nullable', 'url', 'max:255'];
        }

        if (in_array('sort_order', $fields, true)) {
            $rules['sort_order'] = ['required', 'integer', 'min:0', 'max:65535'];
        }

        if (in_array('is_active', $fields, true)) {
            $rules['is_active'] = ['required', 'boolean'];
        }

        if (in_array('value', $fields, true)) {
            $rules['value'] = $this->valueRules((string) $config['type']);
        }

        if (in_array('tone', $fields, true)) {
            $rules['tone'] = $this->toneRules((string) $config['type']);
        }

        return [
            ...$rules,
            'source_record.title' => ['nullable', 'string', 'max:180'],
            'source_record.url' => ['nullable', 'url', 'max:500'],
            'source_record.source_type' => ['required_with:source_record.title,source_record.url', Rule::enum(SourceType::class)],
            'source_record.source_tier' => ['required_with:source_record.title,source_record.url', 'in:tier_1,tier_2,tier_3'],
            'source_record.public_status' => ['required_with:source_record.title,source_record.url', 'in:draft,published,archived,needs_review'],
            'source_record.description' => ['nullable', 'string', 'max:1200'],
            'source_record.verified_at' => ['nullable', 'date'],
            'source_record.next_review_at' => ['nullable', 'date', 'after_or_equal:source_record.verified_at'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->validated();
    }

    /**
     * @return list<mixed>
     */
    private function valueRules(string $type): array
    {
        return match ($type) {
            'project-statuses' => ['required', Rule::enum(ProjectStatusValue::class)],
            'news-statuses' => ['required', Rule::enum(NewsStatusValue::class)],
            'legislative-roles' => ['required', Rule::enum(LegislativeRoleValue::class)],
            default => ['nullable', 'string', 'max:80'],
        };
    }

    /**
     * @return list<mixed>
     */
    private function toneRules(string $type): array
    {
        return match ($type) {
            'project-statuses' => ['required', Rule::enum(ProjectStatusTone::class)],
            'news-statuses' => ['required', Rule::enum(NewsStatusTone::class)],
            'legislative-statuses' => ['required', Rule::enum(LegislativeStatusTone::class)],
            default => ['nullable', 'string', 'max:80'],
        };
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function record(array $config): ?Model
    {
        $record = $this->route('record');

        if (! is_numeric($record)) {
            return null;
        }

        return $config['model']::query()->find((int) $record);
    }
}
