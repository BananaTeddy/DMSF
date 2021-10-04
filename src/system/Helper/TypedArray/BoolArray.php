<?php

declare(strict_types = 1);

namespace system\Helper\TypedArray;

/**
 * A TypedArray for bools
 */
class BoolArray extends TypedArray {
    public function __construct(...$data)
    {
        parent::__construct('boolean', ...$data);
    }
}