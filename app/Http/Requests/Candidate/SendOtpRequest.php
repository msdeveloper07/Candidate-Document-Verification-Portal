<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
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
        return [
            'dial_code' => ['required', 'string', 'max:8', 'regex:/^\+\d{1,4}$/'],
            'phone'     => ['required', 'string', 'min:6', 'max:15'],
        ];
    }

    public function messages(): array
    {
        return [
            'dial_code.regex' => 'Enter a country code such as +44 or +971.',
            'phone.required'  => 'Enter the mobile number you gave the agency.',
        ];
    }
}
