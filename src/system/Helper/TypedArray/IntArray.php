<?php

declare(strict_types = 1);

namespace system\Helper\TypedArray;

/**
 * A TypedArray for integers
 */
class IntArray extends TypedArray {
    public function __construct(...$data)
    {
        parent::__construct('integer', ...$data);
    }
}