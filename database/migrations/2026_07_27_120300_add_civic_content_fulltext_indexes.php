<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE programs ADD FULLTEXT programs_search_ft (title, summary, sector)');
        DB::statement('ALTER TABLE legislative_records ADD FULLTEXT legislative_records_search_ft (bill_number, public_title, official_title, plain_language_summary)');
        DB::statement('ALTER TABLE projects ADD FULLTEXT projects_search_ft (title, summary, barangay, location)');
        DB::statement('ALTER TABLE news_records ADD FULLTEXT news_records_search_ft (title, summary, official_update)');
        DB::statement('ALTER TABLE source_records ADD FULLTEXT source_records_search_ft (title, description, url)');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE programs DROP INDEX programs_search_ft');
        DB::statement('ALTER TABLE legislative_records DROP INDEX legislative_records_search_ft');
        DB::statement('ALTER TABLE projects DROP INDEX projects_search_ft');
        DB::statement('ALTER TABLE news_records DROP INDEX news_records_search_ft');
        DB::statement('ALTER TABLE source_records DROP INDEX source_records_search_ft');
    }
};
