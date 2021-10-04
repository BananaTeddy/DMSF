<?php

declare(strict_types = 1);

namespace system\Helper\TypedArray;

/**
 * A utility class for arrays which ensures that all items are of the same type
 */
class TypedArray implements \ArrayAccess, \Iterator, \JsonSerializable, \Countable {
    protected $type;
    protected $array = [];
    protected $position = 0;
    public $isNullable;

    /**
     * @param $type the type of data the array should hold
     * @param ...$data initial data that can be given optionally
     */
    public function __construct($type, ...$data)
    {
        $this->type = $type;
        $this->isNullable = false;

        $temp = [];
        foreach ($data as $item) {
            if (is_array($item)) {
                foreach ($item as $sub) {
                    $temp[] = $sub;
                }
            } else {
                $temp[] = $item;
            }
        }
        if (! is_null($temp)) {
            $this->set($temp);
        }
    }

    /**
     * Sets the content of the TypedArray to the content of the given array
     * 
     * @param array $data
     * 
     * @return self
     */
    public function set(array $data): TypedArray
    {
        foreach ($data as $key => $item) {
            if (! $this->isValid($item)) {
                throw new \InvalidArgumentException("item at key {$key} is not of type {$this->type}");
            }
            $this->array[] = $item;
        }
        return $this;
    }

    /**
     * Empties the underlying array
     * 
     * @return self
     */
    public function reset(): TypedArray
    {
        $this->array = [];
        return $this;
    }

    /**
     * Sorts the underlying array
     * 
     * @return self
     */
    public function sort(): TypedArray
    {
        sort($this->array);
        return $this;
    }

    /**
     * Sorts the underlying array with a given function
     * 
     * @param callback $sortFunction
     * 
     * @return self
     */
    public function usort(callable $sortFunction): TypedArray
    {
        usort($this->array, $sortFunction);
        return $this;
    }

    /**
     * Filters the underlying array with a given function
     * 
     * @param callback $filterFunction
     * 
     * @return self
     */
    public function filter(callable $filterFunction): TypedArray
    {
        $tmp = array_filter($this->array, $filterFunction);
        $this->array = $tmp;
        return $this;
    }

    /**
     * Reverses the underlying array
     * 
     * @return self
     */
    public function reverse(): TypedArray
    {
        $this->array = array_reverse($this->array);
        return $this;
    }

    /**
     * Returns the underlying php array
     * 
     * Some functions only accept 'true' arrays and not objects that
     * implement ArrayAccess, Iterator, etc.
     * 
     *  @return array
     */
    public function toArray()
    {
        return $this->array;
    }

    /**
     * Make the array printable
     */
    public function __toString()
    {
        return print_r($this->array, true);
    }

    /**
     * Checks if an item is valid
     * 
     * @param mixed $value any item to check
     * 
     * @return bool
     */
    protected function isValid($value): bool
    {
        $typeTest = gettype($value);
        if ($this->type === 'mixed' || $this->type === $typeTest) return true;

        if ($typeTest === 'object') {
            $typeTest = get_class($value);
            if ($this->type === $typeTest) return true;
            if (is_subclass_of($typeTest, $this->type)) return true;
        }

        if (is_null($value)) {
            if ($this->isNullable) return true;
        }

        return false;
    }

    // Implement ArrayAccess functions
    #region ArrayAccess
    public function offsetSet($offset, $value)
    {
        if ($this->isValid($value)) {
            if (is_null($offset)) {
                $this->array[] = $value;
            } else {
                $this->array[$offset] = $value;
            }
        } else {
            throw new \InvalidArgumentException("{$value} is not of type '{$this->type}'");
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->array[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->array[$offset]) ? $this->array[$offset] : null;
    }

    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }
    #endregion ArrayAccess

    // Implement Iterator functions
    #region Iterator
    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->array[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->array[$this->position]);
    }
    #endregion Iterator

    // Implement JsonSerializable functions
    #region JsonSerializable
    public function jsonSerialize()
    {
        return $this->array;
    }
    #endregion JsonSerializable

    // Implement Countable functions
    #region Countable
    public function count()
    {
        return count($this->array);
    }
    #endregion Countable
}
