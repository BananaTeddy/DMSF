<?php declare(strict_types=1);
namespace system\Models;

use JsonSerializable;
use system\Database\QueryBuilder;
use system\Database\QueryFilter;

/**
 * Base class for any model that needs a database binding
 */
abstract class DatabaseModel {
    
    /** @property string $table */
    public static $table;

    /** @var string $primaryKey */
    protected static $primaryKey = 'Id';

    /** @var string[] $columns */
    protected static $columns = [];

    /** @var bool $useTimestamps Does the class save timestamps */
    public $useTimestamps = true;

    /** @var string $created Name of the created at column */
    public static $created = 'created_at';

    /** @var string $modified Name of the modified at column */
    public static $modified = 'modified_at';

    /** @var string $deleted Name of the deleted at column */
    public static $deleted = 'deleted_at';

    /** @var bool $softDeletion Should the database entry be soft deleted (restorable) */
    public $softDeletion = false;

    /** @var bool $markedAsDeleted */
    protected $markedAsDeleted;

    /** @var bool $isDirty gets set to true when a setter was used */
    protected $isDirty = false;

    public function __construct($data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        if (static::$deleted != null) {
            $this->markedAsDeleted == true;
        }
    }

    /**
     * Creates a model based on given data and creates a database entry for it
     *
     * @param array $data associative array with data for the model
     * 
     * @return self
     */
    public static function create($data): ?self {
        $data = array_merge($data, [static::$created => date('Y-m-d H:i:s')]);
        $qb = QueryBuilder::table(static::$table)
            ->values($data);

        $id = $qb->insert();

        if ($qb->getMysqli()->error) throw new \Exception($qb->getMysqli()->error);

        $data[static::$primaryKey] = $id;        

        $instance = new static($data);
        return $instance;
    }

    /**
     * Gets the primary key of the model
     *
     * @return void
     * 
     * @throws \Exception
     **/
    public function getPrimaryKey() {
        $getter = 'get' . static::$primaryKey;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
        throw new \Exception("No [G]etter for Primary Key defined");
    }

    /**
     * Creates a database entry for the object
     *
     * @return true|string
     **/
    public function createDatabaseEntry() {
        $data[static::$primaryKey] = $this->getPrimaryKey();
        foreach (static::$columns as $column) {
            if (isset($this->$column)) {
                $data[$column] = $this->$column;
            }
        }
        $data = array_merge($data, [static::$created => date('Y-m-d H:i:s')]);
        
        $qb = QueryBuilder::table(static::$table)
        ->values($data);
        $qb->insert();

        if ($qb->getMysqli()->error) {
            return $qb->getMysqli()->error;
        } else {
            return true;
        }
    }

    /**
     * Saves changes to the object to the database
     *
     * @return true|string
     **/
    public function save() {
        $data = [];
        foreach (static::$columns as $column) {
            if (isset($this->$column)) {
                $data[$column] = $this->$column;
            }
        }
        if ($this->isDirty) {
            // modify modifydate only if object was modified
            // $data = array_merge($data, [static::$modified => date('Y-m-d H:i:s')]);
        }

        $qb = QueryBuilder::table(static::$table)
        ->set($data)
        ->addFilter([
            QueryFilter::Filter(QueryFilter::Equal, static::$primaryKey, $this->getPrimaryKey())
        ]);
        $qb->update();
        if ($qb->getMysqli()->error) {
            return $qb->getMysqli()->error;
        } else {
            return true;
        }
    }

    /**
     * Deletes the object from the database
     *
     * If softDeletion is true then only marks it as deleted, which
     * will set the deleted column to the current time
     * 
     * @return void
     **/
    public function delete(): void {
        if ($this->softDeletion) {
            $now = date('Y-m-d H:i:s');
            $qb = QueryBuilder::table(static::$table)
            ->set([
                static::$deleted => $now
            ])
            ->addFilter([
                QueryFilter::Filter(
                    QueryFilter::Equal,
                    static::$primaryKey,
                    $this->getPrimaryKey()
                )
            ]);

            $qb->update();
            echo $qb->getMysqli()->error;
            $this->markedAsDeleted = true;
            
            return;
        }
        QueryBuilder::table(static::$table)
        ->addFilter([
            QueryFilter::Filter(QueryFilter::Equal, static::$primaryKey, $this->getPrimaryKey())
        ])
        ->delete();
        return;
    }

    /**
     * Restores soft deleted objects
     * 
     * @return void
     */
    public function restore(): void {
        if ($this->markedAsDeleted) {
            QueryBuilder::table(static::$table)
            ->set([
                static::$deleted => null
            ])
            ->addFilter([
                QueryFilter::Filter(QueryFilter::Equal, static::$primaryKey, $this->getPrimaryKey())
            ])
            ->update();
            $this->markedAsDeleted = false;
            return;
        }
    }
}
