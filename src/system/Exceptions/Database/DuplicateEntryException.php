<?php

declare(strict_types=1);

namespace system\Exceptions\Database;

class DuplicateEntryException extends \Exception {
    public function __construct(string $message = "", int $code = 0xD00B, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}