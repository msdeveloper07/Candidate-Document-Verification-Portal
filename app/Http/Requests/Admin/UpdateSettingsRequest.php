<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'support_phone' => filled($this->support_phone)
                ? preg_replace('/[^\d+\s()-]/', '', (string) $this->support_phone)
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'agency_name'       => ['required', 'string', 'max:120'],
            'agency_tagline'    => ['nullable', 'string', 'max:160'],
            'support_email'     => ['nullable', 'email', 'max:190'],
            'support_phone'     => ['nullable', 'string', 'max:30'],
            // A link that outlives the hiring cycle is a liability; a day is too short to be usable.
            'invite_valid_days' => ['required', 'integer', 'min:1', 'max:90'],
            'otp_ttl_minutes'   => ['required', 'integer', 'min:2', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'agency_name.required'    => 'The agency name appears on every page and email, so it cannot be blank.',
            'invite_valid_days.max'   => 'Upload links should not stay live longer than 90 days.',
            'otp_ttl_minutes.min'     => 'Give candidates at least two minutes to enter their code.',
        ];
    }
}
