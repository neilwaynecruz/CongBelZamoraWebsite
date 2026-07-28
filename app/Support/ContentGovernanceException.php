<?php

namespace App\Support;

use App\Models\ContentApproval;
use RuntimeException;

class ContentGovernanceException extends RuntimeException
{
    /**
     * @param  list<string>  $issues
     */
    public function __construct(
        private readonly ContentApproval $approval,
        private readonly array $issues,
    ) {
        parent::__construct('Content governance validation failed.');
    }

    public function approval(): ContentApproval
    {
        return $this->approval;
    }

    /**
     * @return list<string>
     */
    public function issues(): array
    {
        return $this->issues;
    }
}
