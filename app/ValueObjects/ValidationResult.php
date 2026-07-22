<?php

namespace App\ValueObjects;

class ValidationResult
{
    public function __construct(
        public bool $isValid,
        public ?string $errorMessage = null,
        public ?array $details = null,
    ) {}
}
