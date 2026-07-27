<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckFailedJobs extends Command
{
    protected $signature = 'queue:check-failed {--threshold=1 : Exit non-zero when failed_jobs count is at or above this}';

    protected $description = 'Report failed_jobs count for monitoring and dead-letter awareness.';

    public function handle(): int
    {
        $count = (int) DB::table('failed_jobs')->count();
        $threshold = max(1, (int) $this->option('threshold'));

        $this->info('Failed jobs: '.$count);

        if ($count >= $threshold) {
            Log::warning('failed_jobs threshold reached', [
                'count' => $count,
                'threshold' => $threshold,
            ]);

            $this->warn('Threshold '.$threshold.' reached. Inspect with: php artisan queue:failed');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
