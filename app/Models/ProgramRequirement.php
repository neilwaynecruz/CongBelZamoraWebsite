<?php

namespace App\Models;

use Database\Factories\ProgramRequirementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramRequirement extends Model
{
    /** @use HasFactory<ProgramRequirementFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'program_id',
        'group_title',
        'item_text',
        'note',
        'sort_order',
    ];

    /**
     * @return BelongsTo<Program, $this>
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
