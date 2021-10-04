<?php

declare (strict_types = 1);

namespace application\Users\Models;

use JsonSerializable;
use system\Models\DatabaseModel;

/**
 * A User
 */
class User extends DatabaseModel implements JsonSerializable {
    public static $table = 'users';
    protected static $primaryKey = 'id';
    protected static $columns = [
        'name',
        'email',
        'hashedPassword',
        'isAdmin'
    ];

    /** @var int $id */
    protected $id;

    /** @var string $name */
    protected $name;

    /** @var string $email */
    protected $email;

    /** @var string $hashedPassword */
    protected $hashedPassword;

    /** @var bool $isAdmin */
    protected $isAdmin;

    /**********************************/

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getHashedPassword(): string
    {
        return $this->hashedPassword;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin === 1;
    }

    public function setAdmin(bool $admin): self
    {
        $this->isAdmin = (int) $admin;
        return $this;
    }

    public function grantAdminPrivileges(User $other): self
    {
        if ($this->isAdmin) {
            return $other->setAdmin(true);
        } else {
            throw new \Exception("{$this->name} is not allowed to grant admin privileges");
        }
    }
    
    public function revokeAdminPrivileges(User $other): self
    {
        if ($this->isAdmin) {
            return $other->setAdmin(false);
        } else {
            throw new \Exception("{$this->name} is not allowed to revoke admin privileges");
        }
    }

    #region JsonSerializable
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->displayName
        ];
    }
    #endregion
}