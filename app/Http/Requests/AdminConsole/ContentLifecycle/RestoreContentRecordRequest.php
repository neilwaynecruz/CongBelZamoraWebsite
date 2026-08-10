<?php

namespace App\Http\Requests\AdminConsole\ContentLifecycle;

use Illuminate\Foundation\Http\FormRequest;

class RestoreContentRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'min:12', 'max:500'],
        ];
    }
}
