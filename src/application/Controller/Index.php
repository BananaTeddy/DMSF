<?php

declare (strict_types = 1);

namespace application\Controller;
;
use application\Users\Repositories\UserRepository;
use system\Controller\Controller;

class Index extends Controller
{
    public function index(): void
    {
        $userRepo = new UserRepository();
        $users = $userRepo->getAllUsers();

        $this->view->registerVar('users', $users);
        $this->view->setPage('index/index.tpl');
    }
}