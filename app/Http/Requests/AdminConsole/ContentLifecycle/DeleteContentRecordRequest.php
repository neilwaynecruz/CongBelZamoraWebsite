<?php

namespace App\Http\Requests\AdminConsole\ContentLifecycle;

use Illuminate\Foundation\Http\FormRequest;

class DeleteContentRecordRequest extends FormRequest
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
            'confirmation' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'min:20', 'max:500'],
        ];
    }
}
