<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class StoreReferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $refs = (array) $this->input('references', []);

        foreach ($refs as $slot => $row) {
            $refs[$slot]['phone'] = filled($row['phone'] ?? null)
                ? preg_replace('/\D+/', '', (string) $row['phone'])
                : null;

            $refs[$slot]['dial_code'] = filled($row['dial_code'] ?? null)
                ? '+'.ltrim((string) $row['dial_code'], '+')
                : null;
        }

        $this->merge(['references' => $refs]);
    }

    public function rules(): array
    {
        /*
         * References are optional: a row left completely blank is simply
         * skipped. Only rows the candidate actually started are checked, and
         * then only enough to make sure the reference can be contacted.
         */
        return [
            'references'                => ['nullable', 'array'],
            'references.*.name'         => ['nullable', 'string', 'max:120'],
            'references.*.relationship' => ['nullable', 'string', 'max:120'],
            'references.*.organisation' => ['nullable', 'string', 'max:160'],
            'references.*.email'        => ['nullable', 'email', 'max:190'],
            'references.*.dial_code'    => ['nullable', 'string', 'max:8', 'regex:/^\+\d{1,4}$/'],
            'references.*.phone'        => ['nullable', 'string', 'min:6', 'max:15'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ((array) $this->input('references', []) as $slot => $row) {
                if ($this->rowIsBlank($row)) {
                    continue;
                }

                // A reference with no name is not a reference.
                if (blank($row['name'] ?? null)) {
                    $validator->errors()->add(
                        "references.$slot.name",
                        'Add a name, or clear this reference completely.'
                    );
                }

                // A name we cannot reach is of no use to the recruiter.
                if (blank($row['email'] ?? null) && blank($row['phone'] ?? null)) {
                    $validator->errors()->add(
                        "references.$slot.email",
                        'Add an email address or a phone number for this person.'
                    );
                }
            }
        });
    }

    /** True when the candidate has not typed anything into this slot. */
    private function rowIsBlank(array $row): bool
    {
        foreach (['name', 'relationship', 'organisation', 'email', 'phone'] as $field) {
            if (filled($row[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }

    /** Only the rows worth saving. */
    public function filledReferences(): array
    {
        return array_filter(
            (array) $this->validated('references') ?: [],
            fn ($row) => ! $this->rowIsBlank($row)
        );
    }

    public function messages(): array
    {
        return [
            'references.*.dial_code.regex' => 'Enter a country code such as +1 or +44.',
        ];
    }
}
