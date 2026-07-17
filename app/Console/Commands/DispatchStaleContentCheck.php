<?php

namespace App\Console\Commands;

use App\Jobs\FlagStaleContent;
use Illuminate\Console\Command;

class DispatchStaleContentCheck extends Command
{
    protected $signature = 'content:flag-stale';

    protected $description = 'Queue the public-content freshness check and reviewer digest.';

    public function handle(): int
    {
        FlagStaleContent::dispatch();

        $this->info('Queued public-content freshness check.');

        return self::SUCCESS;
    }
}
