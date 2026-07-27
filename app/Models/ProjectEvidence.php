<?php

namespace App\Models;

use Database\Factories\ProjectEvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectEvidence extends Model
{
    /** @use HasFactory<ProjectEvidenceFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'project_evidences';

    protected $fillable = [
        'project_id',
        'title',
        'source_label',
        'source_url',
        'caption',
        'alt_text',
        'rights_note',
        'media_path',
        'verified_at',
        'verified_by_id',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }
}
