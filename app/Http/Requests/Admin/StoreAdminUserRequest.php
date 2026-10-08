<?php

namespace App\Http\Requests\Admin;

use App\Enums\AdminRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('user')?->id;

        return [
            'name'        => ['required', 'string', 'max:120'],
            'email'       => ['required', 'email', 'max:190', Rule::unique('admins', 'email')->ignore($id)->whereNull('deleted_at')],
            'phone'       => ['nullable', 'string', 'max:32'],
            'designation' => ['nullable', 'string', 'max:120'],
            'role'        => ['required', Rule::in(array_column(AdminRole::cases(), 'value'))],
            'is_active'   => ['nullable', 'boolean'],
            'password'    => [$id ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }
}
