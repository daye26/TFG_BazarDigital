<?php

namespace App\Services;

use InvalidArgumentException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberService
{
    private const DEFAULT_COUNTRY_CODE = '+34';

    private PhoneNumberUtil $phoneNumberUtil;

    public function __construct()
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
    }

    public function normalize(string $countryCode, string $nationalNumber): string
    {
        $phoneNumber = $this->parse(
            $this->normalizeCountryCode($countryCode).$this->normalizeNationalNumber($nationalNumber)
        );

        if (! $this->phoneNumberUtil->isValidNumber($phoneNumber)) {
            throw new InvalidArgumentException('Invalid phone number.');
        }

        return $this->phoneNumberUtil->format($phoneNumber, PhoneNumberFormat::E164);
    }

    public function normalizeOrConcatenate(string $countryCode, string $nationalNumber): string
    {
        $normalizedCountryCode = $this->normalizeCountryCode($countryCode);
        $normalizedNationalNumber = $this->normalizeNationalNumber($nationalNumber);

        try {
            return $this->normalize($normalizedCountryCode, $normalizedNationalNumber);
        } catch (InvalidArgumentException) {
            return $normalizedCountryCode.$normalizedNationalNumber;
        }
    }

    /**
     * @return array{phone_country_code: string, phone_number: string}
     */
    public function split(?string $phone, string $defaultCountryCode = self::DEFAULT_COUNTRY_CODE): array
    {
        $defaultParts = [
            'phone_country_code' => $defaultCountryCode,
            'phone_number' => '',
        ];

        if (! is_string($phone) || trim($phone) === '') {
            return $defaultParts;
        }

        try {
            $phoneNumber = $this->parse($phone);

            if (! $this->phoneNumberUtil->isValidNumber($phoneNumber)) {
                throw new InvalidArgumentException('Invalid phone number.');
            }

            return [
                'phone_country_code' => '+'.$phoneNumber->getCountryCode(),
                'phone_number' => $this->phoneNumberUtil->getNationalSignificantNumber($phoneNumber),
            ];
        } catch (InvalidArgumentException) {
            return $defaultParts;
        }
    }

    private function parse(string $phone): PhoneNumber
    {
        try {
            return $this->phoneNumberUtil->parse($phone, null);
        } catch (NumberParseException $exception) {
            throw new InvalidArgumentException('Invalid phone number.', previous: $exception);
        }
    }

    private function normalizeCountryCode(string $countryCode): string
    {
        return preg_replace('/\s+/', '', trim($countryCode));
    }

    private function normalizeNationalNumber(string $nationalNumber): string
    {
        return preg_replace('/\s+/', '', trim($nationalNumber));
    }
}
