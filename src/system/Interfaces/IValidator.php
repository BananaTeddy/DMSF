<?php

declare(strict_types=1);

namespace system\Interfaces;

interface IValidator {
    public function isValid($data): bool;
}