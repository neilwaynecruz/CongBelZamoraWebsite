<?php

namespace App\Events;

use App\Models\ContentApproval;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentApprovalSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ContentApproval $approval,
        public ?User $actor = null,
    ) {}
}
