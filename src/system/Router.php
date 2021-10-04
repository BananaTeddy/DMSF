<?php declare(strict_types=1);
namespace system;

use configuration\Config;

final class Router {
    
    /**
     * Gets controller, action and parameters based on URL
     *
     * @param string $url the given url
     * @return array
     **/
    public static function route(string $url): Array
    {
        $parts = explode('/', $url, 2);

        // backslashes needed as they act as namespace
        $controller = 'application\\Controller\\' . (strlen($parts[0]) > 0 ? $parts[0] : Config::DEFAULT_CONTROLLER);
        $action = $parts[1] ?? 'index';
        $parameters = array_diff_key($_GET, ['url' => null]);
        
        return [
            $controller,
            $action,
            $parameters
        ];
    }
}