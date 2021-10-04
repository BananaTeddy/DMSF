<?php

declare(strict_types = 1);

namespace system\Helper\TypedArray;

/**
 * A TypedArray for strings
 */
class StringArray extends TypedArray {
    public function __construct(...$data)
    {
        parent::__construct('string', ...$data);
    }
}