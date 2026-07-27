<?php

namespace App\Events;

use App\Models\ContentApproval;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentScheduled
{
    use Dispatchable, SerializesModels;

    public function __construct(public ContentApproval $approval) {}
}
