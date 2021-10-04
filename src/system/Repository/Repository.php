<?php declare(strict_types=1);
namespace system\Repository;

use system\Database\QueryBuilder;
use system\Database\QueryFilter;
use system\Helper\TypedArray\TypedArray;

/**
 * A collection of DatabaseModels
 */
abstract class Repository {
    
    /** @var TypedArray $entries */
    protected $entries = [];

    /** @var string $class */
    public $class;

    /** @var string $table */
    public $table;

    const STRICT_SEARCH = true;

    public function __construct(string $className)
    {
        $this->class = $className;
        $this->table = $className::$table;
        $this->entries = new TypedArray($this->class);
    }

    /**
     * Adds an object to the repository
     * 
     * @param object $model
     * 
     * @return self
     */
    public function add(object $model): self
    {
        $this->entries[] = $model;
        return $this;
    }

    /**
     * Fills entries property with all entries from database
     * 
     * @return self
     */
    public function all(): self
    {
        $this->entries = new TypedArray($this->class);

        $collection = QueryBuilder::table($this->table)->get();

        if (is_iterable($collection)) {
            foreach ($collection as $model) {
                $this->entries[] = new $this->class($model);
            }
        }
        
        return $this;
    }

    /**
     * Sets entries property to an array with a single entry
     * 
     * @param int $offset optional offset
     * 
     * @return self
     */
    public function single(int $offset = 0): self
    {
        $this->entries = new TypedArray($this->class);

        $model = QueryBuilder::table($this->table)
            ->limit(1, $offset)
            ->get();

            $this->entries[] = new $this->class($model);

            return $this;
    }
    

    /**
     * @return TypedArray<DatabaseModel>
     */
    public function __call(string $name, array $arguments)
    {
        // function findBy*
        /**
         * magic method to find database entry by given property
         * @param string property
         * @param mixed propertyvalue
         * @param bool strict_search
         */
        if (substr($name, 0, 6) === "findBy") {
            $property = lcfirst( substr($name, 6) );
            $needle = $arguments[0];
            $strict = $arguments[1] ?? false;
            if (!is_string($needle)) {
                $strict = true;
            }

            $reflection = new \ReflectionClass($this->class);
            $properties = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED);

            foreach ($properties as $key => $value) {
                $properties[$key] = $value->name;
            }

            if (! in_array($property, $properties)) {
                throw new \Exception("{$property} is not a property of {$this->class}");
            }

            $collection = QueryBuilder::table($this->table)
                ->addFilter([
                    QueryFilter::Filter(
                        ($strict ? QueryFilter::Equal : QueryFilter::Like),
                        $property,
                        $needle
                    )
                ])
                ->get();

            $this->entries = new TypedArray($this->class);
            if (is_array($collection)) {
                foreach ($collection as $item) {
                    $this->entries[] = new $this->class($item);
                }
            }

            return $this->entries;
        }
    }

    /**
     * Saves all current entries
     * 
     * @return void
     */
    public function persist(): void
    {
        foreach($this->entries as $entry) {
            $entry->save();
        }
    }
    
    /**
     * Gets all current entries
     * 
     * @return \system\Helper\TypedArray\TypedArray
     */
    public function getEntries(): TypedArray
    {
        return $this->entries;
    }
}
