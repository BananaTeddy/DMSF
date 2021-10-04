<?php declare(strict_types=1);

use system\Controller\Controller;
use system\Event\EventManager;
use system\Exceptions\ControllerNotFoundException;
use system\Exceptions\TemplateNotFoundException;
use system\Helper\Logger;
use system\Template\TemplateEngine;

spl_autoload_register(function ($class) {
    $cwd = getcwd();
    $file = "{$cwd}/src/{$class}.php";
    if (file_exists($file)) {
        require_once $file;
    }
});
spl_autoload_register();

[$controllerName, $action, $parameters] = system\Router::route($_GET['url'] ?? '');

try {
    if (! class_exists($controllerName)) {
        throw new ControllerNotFoundException("Controller {$controllerName} not found");
    }

    $controller = new $controllerName();
    if (! $controller instanceof Controller) {
        throw new \Exception("CONTROLLER_IS_NO_CONTROLLER {$controllerName}");
    }
    if (! method_exists($controller, $action)) {
        throw new \Exception("CONTROLLER_ACTION_NOT_DEFINED {$action}");
    }

    $controller->setEventManager(new EventManager());
    $controller->setEventListeners();
    $controller->setView(new TemplateEngine());
    $controller->doAction($action, $parameters);

} catch (ControllerNotFoundException $e) {
    http_response_code(404);
    $view = new TemplateEngine();
    $view->registerVar('lostPage', htmlspecialchars($_GET['url'] ?? ''));
    $view->setPage('404.tpl')->compile()->display();
    echo "CNF";

} catch (TemplateNotFoundException $e) {
    http_response_code(404);
    $view = new TemplateEngine();
    $view->registerVar('lostPage', htmlspecialchars($_GET['url'] ?? ''));
    $view->setPage('404.tpl')->compile()->display();
    echo "TNF";

} catch (\Exception $e) {
    $logger = new Logger('error.log');
    $logger->start();
    $logger->logException($e);
    $logger->end();
}
