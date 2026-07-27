<?php

namespace App\Services;

use App\Models\LegislativeRecord;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\SourceRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AdminContentSearchService
{
    /**
     * @return Collection<int, array{type: string, title: string, subtitle: string|null, id: int|string}>
     */
    public function search(string $term, int $limit = 25): Collection
    {
        $term = trim($term);

        if ($term === '') {
            return collect();
        }

        return collect([
            ...$this->searchModel(Program::query(), 'Program', ['title', 'summary', 'sector'], $term, $limit),
            ...$this->searchModel(LegislativeRecord::query(), 'Legislation', ['bill_number', 'public_title', 'official_title', 'plain_language_summary'], $term, $limit, 'public_title'),
            ...$this->searchModel(Project::query(), 'Project', ['title', 'summary', 'barangay', 'location'], $term, $limit),
            ...$this->searchModel(NewsRecord::query(), 'News', ['title', 'summary', 'official_update'], $term, $limit),
            ...$this->searchModel(SourceRecord::query(), 'Source', ['title', 'description', 'url'], $term, $limit),
        ])->take($limit)->values();
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  list<string>  $columns
     * @return list<array{type: string, title: string, subtitle: string|null, id: int|string}>
     */
    private function searchModel(Builder $query, string $type, array $columns, string $term, int $limit, string $titleColumn = 'title'): array
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $query->whereFullText($columns, $term);
        } else {
            $query->where(function (Builder $builder) use ($columns, $term): void {
                foreach ($columns as $column) {
                    $builder->orWhere($column, 'like', '%'.$term.'%');
                }
            });
        }

        return $query
            ->limit($limit)
            ->get()
            ->map(fn ($record): array => [
                'type' => $type,
                'title' => (string) ($record->getAttribute($titleColumn) ?? $record->getAttribute('title') ?? class_basename($record).' #'.$record->getKey()),
                'subtitle' => method_exists($record, 'getAttribute') && $record->getAttribute('slug')
                    ? (string) $record->getAttribute('slug')
                    : null,
                'id' => $record->getKey(),
            ])
            ->all();
    }
}
