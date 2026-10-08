<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('document_type')?->id;

        return [
            'name'                   => ['required', 'string', 'max:120', Rule::unique('document_types', 'name')->ignore($id)],
            'description'            => ['nullable', 'string', 'max:190'],
            'instructions'           => ['nullable', 'string', 'max:1000'],
            'allowed_extensions'     => ['required', 'array', 'min:1'],
            'allowed_extensions.*'   => ['string', Rule::in(['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'webp'])],
            'max_size_kb'            => ['required', 'integer', 'min:100', 'max:20480'],
            'is_required_by_default' => ['nullable', 'boolean'],
            'is_active'              => ['nullable', 'boolean'],
            'sort_order'             => ['nullable', 'integer', 'min:0', 'max:9999'],
            'icon'                   => ['nullable', 'string', 'max:64'],
        ];
    }
}
