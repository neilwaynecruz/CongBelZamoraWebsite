<?php

namespace App\Events;

use App\Enums\ContentApprovalStatus;
use App\Models\ContentApproval;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentApprovalDecided
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ContentApproval $approval,
        public ContentApprovalStatus $decision,
        public ?User $actor = null,
    ) {}
}
