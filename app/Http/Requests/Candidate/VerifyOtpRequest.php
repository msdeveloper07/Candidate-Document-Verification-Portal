<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // The form posts six separate boxes; join them into one code.
        if (is_array($this->input('digits'))) {
            $this->merge(['code' => implode('', $this->input('digits'))]);
        }

        $this->merge(['code' => preg_replace('/\D+/', '', (string) $this->input('code'))]);
    }

    public function rules(): array
    {
        $length = config('portal.otp.length');

        return [
            'code' => ['required', 'digits:'.$length],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Enter the code we sent you.',
            'code.digits'   => 'The code is '.config('portal.otp.length').' digits.',
        ];
    }
}
