<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Rules;

use Closure;
use FuteBus\AiAssistant\Support\PhoneNumbers;
use Illuminate\Contracts\Validation\ValidationRule;
use libphonenumber\NumberParseException;

class CustomerPhone implements ValidationRule
{
    public function __construct(private readonly string $country) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^\+?[0-9 ().-]+$/', $value)) {
            $fail(__('AiAssistant::app.phone_invalid'));

            return;
        }
        $util = PhoneNumbers::util();
        try {
            $number = $util->parse($value, $this->country);
            if (! $util->isValidNumberForRegion($number, $this->country) || $number->hasExtension()) {
                $fail(__('AiAssistant::app.phone_invalid'));
            }
        } catch (NumberParseException) {
            $fail(__('AiAssistant::app.phone_invalid'));
        }
    }
}
