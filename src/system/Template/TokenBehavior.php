<?php declare(strict_types=1);
namespace system\Template;

final class TokenBehavior {
    
    private $functions;

    public function __construct()
    {
        $this->functions = [];
    }

    public function __call($method, $arguments)
    {
        $this->functions[$method](...$arguments);
    }
    
    /**
     * Registers a function for the token
     * 
     * @param string $name name of the token
     * @param callback $callback the function to be executed
     * 
     * @return void
     **/
    public function register(string $name, callable $callback): void
    {
        $this->functions[$name] = $callback;
    }

    /**
     * Registers an alias for a token function
     *
     * @param string $original the original token
     * @param string $alias the alias for the function
     * 
     * @return void
     **/
    public function registerAlias(string $original, string $alias): void
    {
        $this->functions[$alias] = $this->functions[$original];
    }
}
