<?php

namespace App\Http\Requests\AdminConsole\Media;

use App\Models\ProjectEvidence;
use Illuminate\Foundation\Http\FormRequest;

class RemoveProjectEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $evidence = $this->route('projectEvidence');

        return $evidence instanceof ProjectEvidence
            && ($this->user()?->can('delete', $evidence) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'confirmation' => ['required', 'string', 'in:remove evidence record'],
        ];
    }
}
