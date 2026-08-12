<?php

namespace App\Http\Requests\AdminConsole\Media;

use App\Models\NewsMediaAsset;
use Illuminate\Foundation\Http\FormRequest;

class RemoveNewsMediaAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $asset = $this->route('newsMediaAsset');

        return $asset instanceof NewsMediaAsset
            && ($this->user()?->can('delete', $asset) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'confirmation' => ['required', 'string', 'in:remove media asset'],
        ];
    }
}
