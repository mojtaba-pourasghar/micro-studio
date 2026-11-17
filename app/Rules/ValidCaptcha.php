<?php

namespace App\Rules;

use App\Support\Captcha;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCaptcha implements ValidationRule
{
    public function __construct(protected string $key) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Captcha::check($this->key, is_scalar($value) ? (string) $value : null)) {
            $fail('کد امنیتی درست نیست؛ کد تازه را وارد کنید.');
        }
    }
}
