<?php

namespace App\Support;

trait ReliableQueuedWork
{
    public int $tries;

    /** @var list<int>|int */
    public int|array $backoff;

    public int $timeout;

    public function configureReliableQueue(): void
    {
        $this->tries = (int) config('belzamora.jobs.default_tries', 3);
        $this->backoff = (int) config('belzamora.jobs.default_backoff_seconds', 60);
        $this->timeout = (int) config('belzamora.jobs.default_timeout_seconds', 120);
    }
}
