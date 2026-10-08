<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type_id' => ['required', 'integer', 'exists:document_types,id'],
            'file'             => ['required', 'file', 'max:'.config('portal.uploads.max_size_kb')],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Choose a file to upload.',
            'file.max'      => 'That file is too large.',
        ];
    }
}
