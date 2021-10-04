<?php

declare(strict_types = 1);

namespace system\Helper\TypedArray;

/**
 * A TypedArray for doubles
 */
class DoubleArray extends TypedArray {
    public function __construct(...$data)
    {
        parent::__construct('double', ...$data);
    }
}