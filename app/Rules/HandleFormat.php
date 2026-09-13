<?php

namespace App\Rules;

use App\Models\Handle;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HandleFormat implements ValidationRule
{
    public function __construct(protected ?Handle $handle) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->handle || ! is_string($value) || $this->handle->matches($value)) {
            return;
        }

        $fail($this->handle->regex_hint ?: "The {$this->handle->label} handle format is invalid.");
    }
}
