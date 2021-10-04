<?php

declare(strict_types = 1);

namespace system\Helper\TypedArray;

/**
 * A TypedArray for floating point numbers
 */
class FloatArray extends TypedArray {
    public function __construct(...$data)
    {
        parent::__construct('float', ...$data);
    }
}