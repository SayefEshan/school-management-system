<?php

namespace Modules\User\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class PhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^88[0-9]{10,11}$/', $value)) {
            $fail("The {$attribute} must be a valid phone number. Example: 880XXXXXXXXX");
        }
    }
}
