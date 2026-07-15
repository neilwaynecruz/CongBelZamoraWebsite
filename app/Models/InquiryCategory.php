<?php

namespace App\Models;

use Database\Factories\InquiryCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InquiryCategory extends Model
{
    /** @use HasFactory<InquiryCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'label',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
