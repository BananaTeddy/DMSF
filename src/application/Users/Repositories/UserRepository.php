<?php

declare (strict_types = 1);

namespace application\Users\Repositories;

use application\Users\Models\User;
use system\Repository\Repository;
use system\Database\QueryBuilder;
use system\Database\QueryFilter;
use system\Helper\TypedArray\TypedArray;

/**
 * A Collection of Users
 */
class UserRepository extends Repository {
    public function __construct()
    {
        parent::__construct(User::class);
    }

    /**
     * returns all users in a TypedArray
     */
    public function getAllUsers(): TypedArray
    {
        $qb = QueryBuilder::table($this->table)
        ->select();

        $dataset = $qb->get();

        $users = new TypedArray(User::class);

        if (is_iterable($dataset)) {
            foreach ($dataset as $data) {
                $users[] = new $this->class($data);
            }
        }

        return $users;
    }

    /**
     * Returns a user with a specific id
     * 
     * @param int $id The id to look for
     * 
     * @return User
     * 
     * @throws Exception
     */
    public function findById(int $id): User
    {
        $userData = QueryBuilder::table($this->table)
        ->select()
        ->addFilter([
            QueryFilter::Filter(
                QueryFilter::Equal,
                'id',
                $id
            )
        ])
        ->first()
        ->get();

        if (isset($userData)) {
            return new $this->class($userData);
        } else {
            throw new \Exception("Invalid User-Id: {$id}");
        }
    }

    /**
     * Returns a user with a specific name or null if no user is found
     * 
     * @param string $name The name of the user to return
     * 
     * @return User|null
     */
    public function findByName(string $name): ?User
    {
        $userData = QueryBuilder::table($this->table)
        ->select()
        ->addFilter([
            QueryFilter::Filter(
                QueryFilter::Equal,
                'name',
                $name
            )
        ])
        ->first()
        ->get();

        if (isset($userData)) {
            return new $this->class($userData);
        } else {
            return null;
        }
    }
}