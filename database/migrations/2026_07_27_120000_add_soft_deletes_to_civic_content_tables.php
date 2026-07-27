<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Soft deletes for main civic content and supporting child records.
     * Reference-data tables remain deactivate-first (is_active) without soft deletes.
     *
     * @var list<string>
     */
    private array $tables = [
        'public_pages',
        'programs',
        'program_requirements',
        'program_updates',
        'service_guides',
        'service_guide_steps',
        'legislative_records',
        'legislative_source_histories',
        'projects',
        'project_evidences',
        'news_records',
        'news_media_assets',
        'source_records',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }
    }
};
