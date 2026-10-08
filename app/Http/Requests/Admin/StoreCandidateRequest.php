<?php

namespace App\Http\Requests\Admin;

use App\Enums\Availability;
use App\Enums\PreferredShift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;

class StoreCandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone'     => preg_replace('/\D+/', '', (string) $this->input('phone')),
            'dial_code' => '+'.ltrim((string) $this->input('dial_code'), '+'),
        ]);
    }

    public function rules(): array
    {
        $candidateId = $this->route('candidate')?->id;

        return [
            'first_name'       => ['required', 'string', 'max:80'],
            'middle_name'      => ['nullable', 'string', 'max:80'],
            'last_name'        => ['required', 'string', 'max:80'],
            // 16 is a floor no employer would ever hire below; 100 catches typos like 1092.
            'date_of_birth'    => ['nullable', 'date', 'before:'.now()->subYears(16)->toDateString(), 'after:'.now()->subYears(100)->toDateString()],
            'email'            => ['required', 'email', 'max:190'],
            'dial_code'        => ['required', 'string', 'max:8', 'regex:/^\+\d{1,4}$/'],
            'phone'            => [
                'required', 'string', 'min:6', 'max:15',
                Rule::unique('candidates', 'phone')
                    ->where(fn ($q) => $q->where('dial_code', $this->input('dial_code')))
                    ->ignore($candidateId)
                    ->whereNull('deleted_at'),
            ],
            'country_code'     => ['nullable', 'string', 'size:2'],
            'country_name'     => ['nullable', 'string', 'max:80'],
            'address_line1'    => ['nullable', 'string', 'max:190'],
            'address_line2'    => ['nullable', 'string', 'max:190'],
            'city'             => ['nullable', 'string', 'max:120'],
            'state'            => ['nullable', 'string', 'max:120'],
            'postal_code'      => ['nullable', 'string', 'max:20'],
            'position_applied' => ['nullable', 'string', 'max:150'],
            'availability'     => ['nullable', new Enum(Availability::class)],
            'available_from'   => ['nullable', 'date', 'after_or_equal:today'],
            'preferred_shift'  => ['nullable', new Enum(PreferredShift::class)],
            'internal_notes'   => ['nullable', 'string', 'max:2000'],
            'requirements'     => ['required', 'array', 'min:1'],
            'requirements.*'   => ['integer', 'exists:document_types,id'],
            'send_invite'      => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique'         => 'A candidate with this mobile number already exists.',
            'date_of_birth.before'  => 'The date of birth must make the candidate at least 16.',
            'date_of_birth.after'   => 'Check the date of birth — that year looks wrong.',
            'available_from.after_or_equal' => 'The available-from date cannot be in the past.',
            'dial_code.regex'    => 'Enter a country code such as +44 or +971.',
            'requirements.min'   => 'Select at least one document to request.',
            'requirements.required' => 'Select at least one document to request.',
        ];
    }

    public function candidateData(): array
    {
        return $this->safe()->only([
            'first_name', 'middle_name', 'last_name', 'date_of_birth', 'email',
            'dial_code', 'phone', 'country_code', 'country_name',
            'address_line1', 'address_line2', 'city', 'state', 'postal_code',
            'position_applied', 'availability', 'available_from', 'preferred_shift',
            'internal_notes',
        ]);
    }
}
