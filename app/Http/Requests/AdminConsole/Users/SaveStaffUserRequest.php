<?php

namespace App\Http\Requests\AdminConsole\Users;

use App\Enums\AdminRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class SaveStaffUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $staffUser = $this->route('user');

        if ($staffUser instanceof User) {
            return $this->user()?->can('update', $staffUser) ?? false;
        }

        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $staffUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($staffUser instanceof User ? $staffUser->id : null),
            ],
            'admin_role' => ['required', new Enum(AdminRole::class)],
            'is_active' => ['boolean'],
            'password' => [
                $staffUser instanceof User ? 'nullable' : 'required',
                'string',
                Password::defaults(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $validated = $this->validated();
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        if (! filled($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        return $validated;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'admin_role.required' => 'Choose the exact staff role before saving access.',
            'password.required' => 'Create a temporary password for the first staff sign-in.',
        ];
    }
}
