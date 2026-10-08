<?php

namespace App\Http\Requests\Admin;

use App\Models\EmailTemplate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject'      => ['required', 'string', 'max:190'],
            'heading'      => ['required', 'string', 'max:190'],
            'body'         => ['required', 'string', 'max:4000'],
            'button_label' => ['nullable', 'string', 'max:60'],
            'footer_note'  => ['nullable', 'string', 'max:400'],
            'is_active'    => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var EmailTemplate $template */
            $template = $this->route('email_template');
            $allowed  = array_keys(EmailTemplate::placeholders($template->key));

            foreach (['subject', 'heading', 'body', 'button_label', 'footer_note'] as $field) {
                preg_match_all('/\{\{\s*([\w.]+)\s*\}\}/', (string) $this->input($field), $m);

                $unknown = array_diff(array_unique($m[1]), $allowed);

                if ($unknown) {
                    $validator->errors()->add(
                        $field,
                        'Unknown placeholder: '.implode(', ', array_map(fn ($u) => '{{ '.$u.' }}', $unknown))
                        .'. Use one of the tags listed beside the editor.'
                    );
                }
            }
        });
    }
}
