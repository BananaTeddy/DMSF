<?php

declare (strict_types = 1);

namespace application\Controller;

use system\Controller\Controller;

abstract class API_Controller extends Controller {
    protected function beforeAction(): void
    {
        parent::beforeAction();

        echo json_encode([
            'success' => false,
            'errors' => [
                'Access denied'
            ]
        ]);
        exit;
    }

    protected function afterAction(): void {} // empty as we dont want to output any templates
}