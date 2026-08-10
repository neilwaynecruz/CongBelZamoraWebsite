<?php

namespace App\Http\Requests\AdminConsole\ContentApprovals;

use App\Models\ContentApproval;
use Illuminate\Foundation\Http\FormRequest;

abstract class ContentApprovalActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $approval = $this->route('contentApproval');

        return $approval instanceof ContentApproval
            && $this->user()?->can($this->ability(), $approval) === true;
    }

    abstract protected function ability(): string;
}
