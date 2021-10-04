<?php

declare (strict_types = 1);

namespace application\Controller;

use application\Users\Models\User;
use system\Controller\Controller;
use system\Database\QueryBuilder;

class Installation extends Controller {
    private $userTable;

    public function __construct()
    {
        $this->userTable = 'users';
    }


    public function Install()
    {
        // USERS
        // --------------------------------------------------------------- //
        QueryBuilder::raw("CREATE TABLE `{$this->userTable}` (
            `id`int(11) NOT NULL,
            `name` varchar(255) DEFAULT NULL,
            `email` varchar(255) NOT NULL,
            `hashedPassword` varchar(255) NOT NULL,
            `isAdmin` boolean NOT NULL DEFAULT 0,
            `created_at` datetime NOT NULL,
            `modified_at` datetime DEFAULT NULL,
            `deleted_at` datetime DEFAULT NULL
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8");
        
        QueryBuilder::raw("ALTER TABLE `{$this->userTable}`
        ADD PRIMARY KEY (`id`);");

        QueryBuilder::raw("ALTER TABLE `{$this->userTable}`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

        QueryBuilder::raw("ALTER TABLE `{$this->userTable}`
        ADD UNIQUE(`email`);");

        // ADD SAMPLE DATA
        #region SampleData
        /** @var User $user1 */
        $user1 = User::create([
            'email' => 'bananateddy@bananateddy.com',
            'name' => 'BananaTeddy',
            'hashedPassword' => password_hash(
                htmlspecialchars('password'),
                PASSWORD_DEFAULT
            ),
            'isAdmin' => 1
        ]);

        $user2 = User::create([
            'email' => 'dmsf_example@bananateddy.com',
            'name' => 'DMSF Example',
            'hashedPassword' => password_hash(
                htmlspecialchars('password'),
                PASSWORD_DEFAULT
            ),
            'isAdmin' => 0
        ]);
        #endregion SampleData

        $this->redirect('Index/index');

    }
}