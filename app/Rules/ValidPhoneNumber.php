<?php

namespace App\Rules;

use App\Services\PhoneNumberService;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;

class ValidPhoneNumber implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $countryCode = (string) ($this->data['phone_country_code'] ?? '');
        $nationalNumber = (string) ($this->data['phone_number'] ?? '');

        if ($countryCode === '' || $nationalNumber === '') {
            return;
        }

        try {
            app(PhoneNumberService::class)->normalize($countryCode, $nationalNumber);
        } catch (InvalidArgumentException) {
            $fail('Introduce un telefono valido.');
        }
    }
}
