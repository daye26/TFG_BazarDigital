<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalizedCountryCode = preg_replace('/\s+/', '', (string) $this->input('phone_country_code'));
        $normalizedPhoneNumber = preg_replace('/\s+/', '', (string) $this->input('phone_number'));

        $this->merge([
            'phone_country_code' => $normalizedCountryCode,
            'phone_number' => $normalizedPhoneNumber,
            'phone' => $normalizedCountryCode.$normalizedPhoneNumber,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone_country_code' => ['required', 'string', 'regex:/^\+[1-9]\d{0,3}$/'],
            'phone_number' => ['required', 'string', 'regex:/^\d{6,14}$/'],
            'phone' => ['required', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
        ];
    }
}
