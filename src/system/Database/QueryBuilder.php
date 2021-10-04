<?php declare(strict_types=1);
namespace system\Database;

use mysqli;
use stdClass;
use system\Exceptions\Database\DuplicateEntryException;

final class QueryBuilder {
    
    private $mysqli;
    private $table;

    private $selection;
    private $first;
    private $last;
    private $distinct;
    private $sets;
    private $values;
    private $conditions;
    private $orderBy;
    private $limit;
    private $joins;

    private function __construct(string $name, array $info = null)
    {
        $this->table = $name;
        $this->mysqli = Database::connect($info);
        $this->joins = [];
    }

    public function __destruct()
    {
        if ($this->mysqli instanceof \mysqli) {
            $this->mysqli->close();
        }
    }
    
    /**
     * Returns a new instance of the QueryBuilder fixated on the given table
     * 
     * All subsequent sql functions will be executed on the given table.
     * A set of seperate connection information can be given to make the
     * builder connect to another database. If no information is given
     * the default database specified in the config will be used.
     * 
     * @param string $name
     * @param array|null $info Default: null
     * 
     * @return self
     */
    public static function table(string $name, array $info = null): self
    {
        return new static($name, $info);
    }

    /**
     * Executes a query string
     * A set of seperate connection information can be given to make the
     * query to another database. If no information is given
     * the default database specified in the config will be used.
     * 
     * @param string $query
     * @param array $info Default: null
     * 
     * @return stdClass returns a stdClass with the result and the mysqli object
     */
    public static function raw(string $query, array $info = null): stdClass
    {
        $mysqli = Database::connect($info);
        $result = $mysqli->query($query);

        $return = new stdClass();
        $return->result = $result;
        $return->mysqli = $mysqli;
        return $return;
    }

    /**
     * Determines which fields should be selected
     * 
     * If there is no argument present then simply select all fields.
     * If the argument is a string then just use it as given with htmlentities applied
     * If the argument is an array then add all elements. For any associative entries
     * make the key the field and the value the alias
     * 
     * @param string|string[]|null $selection Default: null
     * 
     * @return self
     */
    public function select($selection = null): self
    {
        if (is_null($selection)) $this->selection .= '*';
        if (is_string($selection)) $this->selection .= htmlentities($selection);
        if (is_array($selection)) {
            foreach ($selection as $key => $value) {
                if (is_string($key)) {
                    $this->selection .= "{$key} AS {$value}, ";
                } else {
                    $this->selection .= "{$value}, ";
                }
            }
            $this->selection = rtrim($this->selection, ', ');
        }

        return $this;
    }

    /**
     * Counts entries of the given argument
     * 
     * @param string|string[]|null $countable Default: null
     * 
     * @return self
     */
    public function count($countable = null): self
    {
        if (is_null($countable)) $this->selection .= 'COUNT(*)';
        if (is_string($countable)) $this->selection .= "COUNT({$countable})";
        if (is_array($countable)) {
            foreach ($countable as $key => $value) {
                if (is_string($key)) {
                    $this->selection .= "COUNT({$key}) AS {$value}, ";
                } else {
                    $this->selection .= "COUNT {$value}, ";
                }
            }
            $this->selection = rtrim($this->selection, ', ');
        }
        return $this;
    }

    /**
     * The query should only select distinct entries
     * 
     * @param bool $makeDistinction Default: true
     * 
     * @return self
     */
    public function distinct(bool $makeDistinction = true): self
    {
        $this->distinct = $makeDistinction;
        return $this;
    }

    /**
     * Flags the builder to return only the first element and sets limit to 1
     * 
     * @return self
     */
    public function first(): self
    {
        $this->first = true;
        $this->last = false;
        $this->limit(1);
        return $this;
    }

    /** 
     * Flags the builder to return only the last element
     * 
     * @return self
     */
    public function last(): self
    {
        $this->last = true;
        $this->first = false;
        return $this;
    }

    /**
     * Joins another table
     * 
     * @param string $tableName
     * @param string $prop1
     * @param string $prop2
     * @param string $joinType Default: INNER
     * 
     * @return self
     */
    public function join(string $tableName, string $prop1, string $prop2, string $joinType = 'INNER'): self
    {
        $joinType = strtoupper($joinType);
        $allowedJoinTypes = [
            'INNER', 'OUTER', 'LEFT', 'RIGHT'
        ];
        if (! in_array($joinType, $allowedJoinTypes)) {
            throw new \Exception("Unexpected JOIN-Type");

        }
        $this->joins[] = "{$joinType} JOIN {$tableName} ON {$prop1} = {$prop2}";

        return $this;
    }

    /**
     * Set these fields to values provided by values
     * 
     * @param array $sets
     * 
     * @return self
     */
    public function set(array $sets)
    {
        $this->sets = $sets;
        return $this;
    }

    /**
     * Set fields for insertion
     * 
     * @param array $values
     * 
     * @return self
     */
    public function values(array $values): self
    {
        $this->values = $values;
        return $this;
    }

    /**
     * Tells the query builder to add the following filters
     * 
     * Use the QueryFilter class filters
     * 
     * @param array $filters
     * 
     * @return self
     */
    public function addFilter(array $filters): self
    {
        $conditions = "WHERE ";
        foreach ($filters as $key => $value) {
            $conditions .= " {$value} ";
        }
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * Order the results by the given fields in the given order
     * 
     * @param string|string[] $props
     * @param string $order Default: ASC
     * 
     * @return self
     */
    public function orderBy($props, $order = 'ASC'): self
    {
        $whitelist = ['ASC', 'DESC'];

        if (is_string($order)) {
            if (! in_array($order, $whitelist))
                throw new \Exception("{$order} is not a valid argument for orderBy.");
        } else if (is_array($order)) {

            foreach ($order as $index => $value) {
                if (! in_array($value, $whitelist))
                    throw new \Exception("{$order[$index]} is not a valid argument for orderBy at index {$index}.");
            }
        } else {
            throw new \Exception("\$order argument for QueryBuilder->orderBy() is of wrong type.");
        }

        if (! (is_array($props) || is_string($props))) {
            throw new \Exception("\$props argument for QueryBuilder->orderBy() is of wrong type");
        }

        $orderBy = ' ORDER BY ';
        if (is_string($props) && is_string($order)) {
            $orderBy .= "{$props} {$order} ";
        } else if (is_array($props) && is_string($order)) {
            foreach ($props as $prop) {
                $orderBy .= "{$prop} {$order}, ";
            }
        } else if (is_array($props) && is_array($order)) {
            foreach ($props as $index => $prop) {
                $orderBy .= "{$prop} {$order[$index]}, ";
            }
        }
        $orderBy = rtrim($orderBy, ', ');
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * Limits the result returned by the database
     * 
     * @param int $limitation
     * @param int $offset Default: 0
     * 
     * @return self
     */
    public function limit(int $limitation, int $offset = 0): self
    {
        $this->limit = "LIMIT {$offset}, {$limitation} ";
        return $this;
    }

    /**
     * Builds the select query and returns the result
     * 
     * @return mixed|array|null|false
     */
    public function get()
    {
        $query = 'SELECT ';
        $query .= $this->distinct ? ' DISTINCT ' : '';
        $query .= $this->selection ?? '*';
        $query .= " FROM {$this->table} ";
        foreach ($this->joins as $join) {
            $query .= " {$join} ";
        }
        $query .= $this->conditions ?? '';
        $query .= $this->orderBy ?? '';
        $query .= $this->limit ?? '';

        $this->stripQuery($query);

        $result = $this->mysqli->query($query);

        if ($result) {
            if ($result->num_rows == 0) return null;
    
            $collection = $this->makeCollection($result);
    
            if ($this->first)
                return $collection[0];
            else if ($this->last)
                return end($collection);
            else
                return $collection;
        } else {
            return false;
        }

    }

    /**
     * Dumps the get() function as text
     * 
     * @return string
     */
    public function dumpGet(): string
    {
        $query = 'SELECT ';
        $query .= $this->distinct ? ' DISTINCT ' : '';
        $query .= $this->selection ?? '*';
        $query .= " FROM {$this->table} ";
        foreach ($this->joins as $join) {
            $query .= " {$join} ";
        }
        $query .= $this->conditions ?? '';
        $query .= $this->orderBy ?? '';
        $query .= $this->limit ?? '';

        $this->stripQuery($query);

        return $query;
    }

    /**
     * Builds update query and executes it
     * 
     * Returns the number of rows affected
     * 
     * @return int
     */
    public function update(): int
    {
        $query = $this->buildUpdateQuery();

        $this->mysqli->query($query);

        $affected = $this->mysqli->affected_rows;

        return $affected;
    }


    /**
     * Builds the update query
     * 
     * @return string
     */
    private function buildUpdateQuery(): string
    {
        $query = "UPDATE {$this->table} ";
        $query .= 'SET ';
        foreach ($this->sets as $key => $value) {

            if (is_null($value)) {
                $query .= "{$key} = NULL, ";
            } else if (is_string($value)) {
                $query .= sprintf(
                    "%s = '%s', ",
                    $key,
                    $this->mysqli->real_escape_string($value)
                );
            } else {
                $query .= "{$key} = {$value}, ";
            }
        }
        $query = rtrim($query, ', ');
        $query .= ' ';
        $query .= $this->conditions ?? '';
        $this->stripQuery($query);

        return $query;
    }

    /**
     * Dumps the update query as text;
     * 
     * @return string
     */
    public function dumpUpdate(): string
    {
        return $this->buildUpdateQuery();
    }

    /**
     * Builds the insert query and executes it
     * 
     * @return int|string
     */
    public function insert()
    {
        $query = "INSERT INTO {$this->table} (";
        foreach ($this->values as $prop => $value) {
            $query .= "{$prop}, ";
        }
        $query = rtrim($query, ', ');
        $query .= ") VALUES (";
        foreach ($this->values as $prop => $value) {
            if (is_string($value)) {
                $query .= "'{$value}', ";
            } else if (is_null($value)) {
                $query .= "NULL, ";
            } else {
                $query .= "{$value}, ";
            }
        }
        $query = rtrim($query, ', ');
        $query .= ")";
        $this->stripQuery($query);

        $this->mysqli->query($query);

        if ($this->mysqli->error) {
            if (stripos($this->mysqli->error, 'duplicate entry') !== false) {
                throw new DuplicateEntryException($this->mysqli->error);
            }
        }

        return $this->mysqli->insert_id;
    }

    /**
     * Builds delete query and executes it
     * 
     * @return int
     */
    public function delete(): int
    {
        $query = "DELETE FROM {$this->table} ";
        $query .= $this->conditions ?? '';

        $this->mysqli->query($query);
        return $this->mysqli->affected_rows;
    }

    /** 
     * Divides the select query into chunks of chunkSize and executes a callable on them
     * 
     * @param int $chunkSize
     * @param callback $callback
     * 
     * @return void
     */
    public function chunk(int $chunkSize, callable $callback): void
    {
        $offset = 0;
        $this->limit($chunkSize, $offset);
        $result = true;
        while ($result) {
            $collection = $this->get();
            if (is_null($collection)) return;
            if (count($collection) > 0) {
                $result = $callback($collection);
                $offset += $chunkSize;
                $this->limit($chunkSize, $offset);
            } else {
                $result = false;
            }
        }
    }

    /**
     * Strips the query from unneccessary white space
     * 
     * @param string &$query
     */
    private function stripQuery(string &$query): void
    {
        // clean up query
        // backup values within single or double quotes
        preg_match_all('/(\'[^\']*?\'|"[^"]*?")/ims', $query, $hit, PREG_PATTERN_ORDER);
        for ($i=0; $i < count($hit[1]); $i++) {
          $query = str_replace($hit[1][$i], '##########' . $i . '##########', $query);
        }
        $query = preg_replace('/ {2,}/', ' ', $query);
        // Restore backupped values within single or double quotes
        for ($i=0; $i < count($hit[1]); $i++) {
          $query = str_replace('##########' . $i . '##########', $hit[1][$i], $query);
        }

        $query = trim($query);
    }

    /**
     * Turns the mysqli_result into an array
     * 
     * @param \mysqli_result $result
     * 
     * @return array
     */
    private function makeCollection(\mysqli_result $result): array
    {
        $collection = [];
        while ($row = $result->fetch_assoc()) {
            $collection[] = $row;
        }
        return $collection;
    }
    
    /**
     * gets the underlying mysqli object
     */
    public function getMysqli(): \mysqli
    {
        return $this->mysqli;
    }

}
