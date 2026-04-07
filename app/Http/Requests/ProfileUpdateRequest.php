<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\ValidPhoneNumber;
use App\Services\PhoneNumberService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalizedCountryCode = preg_replace('/\s+/', '', (string) $this->input('phone_country_code'));
        $normalizedPhoneNumber = preg_replace('/\s+/', '', (string) $this->input('phone_number'));
        $phoneNumberService = app(PhoneNumberService::class);

        $this->merge([
            'phone_country_code' => $normalizedCountryCode,
            'phone_number' => $normalizedPhoneNumber,
            'phone' => $phoneNumberService->normalizeOrConcatenate($normalizedCountryCode, $normalizedPhoneNumber),
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
            'phone_country_code' => ['bail', 'required', 'string', 'regex:/^\+[1-9]\d{0,2}$/'],
            'phone_number' => ['bail', 'required', 'string', 'regex:/^\d{2,14}$/'],
            'phone' => [
                'bail',
                'required',
                'string',
                new ValidPhoneNumber,
                Rule::unique(User::class, 'phone')->ignore($this->user()->id),
            ],
        ];
    }
}
