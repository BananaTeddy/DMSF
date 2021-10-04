<?php declare(strict_types=1);
namespace system\Template;

use \configuration\Config;
use system\Helper\TypedArray\TypedArray;

final class TemplateEngine {
    
    /**
     * @var Page $page
     */
    private $page;

    /**
     * @var string $template
     */
    private $template;

    /**
     * @var array $templateVars
     */
    private $templateVars;

    /**
     * @var TokenBehavior $tokenBehavior
     */
    private $tokenBehavior;

    /**
     * @var Token $tokenTree
     */
    private $tokenTree;

    /**
     * @var array $capturing
     */
    private $capturing;

    /**
     * @var bool $minify
     */
    private $minify;


    public function __construct()
    {
        $this->page = null;
        $this->template = null;
        $this->templateVars = [];
        $this->tokenBehavior = new TokenBehavior();
        $this->tokenTree = null;
        $this->capturing = [
            'foreach',
            'range',
            'if'
        ];
        $this->minify = false;

        $this->setupTokenFunctions();
    }

    /**
     * Compiles the template
     * 
     * Resolves the given template and translates it to php. 
     * 
     * @return self
     */
    public function compile(): self
    {
        $cwd = getcwd();
        if (! file_exists("{$cwd}/cache/Templates/{$this->page->getName()}.php") || Config::CURRENT_ENV == Config::ENVIRONMENT_DEBUG) {
            $pos = strrpos($this->page->getName(), '/');
            if ($pos !== false) {
                $structure = substr($this->page->getName(), 0, $pos);
                $dir = "{$cwd}/cache/Templates/{$structure}";
                if (!is_dir($dir))
                    mkdir($dir, 0774, true);
            }

            $this->template = $this->page->getFullyResolvedTemplate();
            $this->processTemplate();
            $this->processTokens($this->tokenTree);
            $this->cleanupEndTokens();
            $this->minify();
            file_put_contents("{$cwd}/cache/Templates/{$this->page->getName()}.php", $this->template);
                
        }

        if (! file_exists("{$cwd}/cache/JavaScript/javascript.min.js")) {
            $url = Config::JAVASCRIPT_MINIFIER;

            $cwd = getcwd();
            include_once("{$cwd}/templates/src/javascript/_include.php");
            $jsFiles = $javascriptFiles;
            $js = "";
            foreach ($jsFiles as $jsFile) {
                $js .= file_get_contents("{$cwd}/templates/src/javascript/{$jsFile}");
            }
            file_put_contents("{$cwd}/javascript.js", $js);
            

            // init the request, set various options, and send it
            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
                CURLOPT_POSTFIELDS => http_build_query([ "input" => $js ])
            ]);



            $minified = curl_exec($ch);

            // finally, close the request
            curl_close($ch);

            // output the $minified JavaScript
            file_put_contents("{$cwd}/cache/JavaScript/javascript.min.js", $minified);
        }
        return $this;
    }

    /**
     * Extracts all registered variables to the page and displays it
     * 
     * @return void
     */
    public function display(): void
    {
        $cwd = getcwd(); // current working directory
        extract($this->templateVars);
        include("{$cwd}/cache/Templates/{$this->page->getName()}.php");
    }

    /**
     * Gets the HTML of a template as a string
     * 
     * @return string
     */
    public function getHTML(): string
    {
        $cwd = getcwd();
        extract($this->templateVars);
        ob_start();
            include("$cwd/cache/Templates/{$this->page->getName()}.php");
            $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    /**
     * Registers a variable to the template
     * 
     * @param string $name
     * @param mixed|null $value
     * 
     * @return void
     */
    public function registerVar(string $name, $value): void
    {
        $this->templateVars[$name] = $value;
    }

    /**
     * Unregisters a template variable
     * 
     * @param string $name
     * 
     * @return void
     */
    public function unsetVar(string $name): void
    {
        unset($this->templateVars[$name]);
    }

    /**
     * Registers a function for a token
     * 
     * @param string $name
     * @param callback $callback
     * 
     * @return void
     */
    public function registerTokenFunction(string $name, callable $callback): void
    {
        $this->tokenBehavior->register($name, $callback);
    }

    /**
     * Registers an alias for a given token function
     * 
     * @param string $original
     * @param string $alias
     * 
     * @return void
     */
    public function registerTokenAlias(string $original, string $alias): void
    {
        $this->tokenBehavior->registerAlias($original, $alias);
    }

    /**
     * Processes the template
     * 
     * Parses tokens in the template
     * 
     * @return void
     */
    private function processTemplate(): void
    {
        $tokens = new TypedArray(Token::class);
        $pattern = "/\{\{(end)? ?(.+?)\}\}/";
        $lines = explode("\n", $this->template);
        foreach ($lines as $num => $line) {
            if  (preg_match_all($pattern, $line, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $arr = explode(" ", $match[2], 2);
                    // echo "<pre>" . print_r($arr, true) . "</pre>";
                    $type = $arr[0];
                    $arguments = implode("=", $arr);
                    // echo "{$arguments}<br>";
                    $arguments = str_replace("{$type}=", "", $arguments);
                    // echo "{$arguments}<hr>";
                    $closing = $match[1] == "end" ? true: false;
                    $tokens[] = new Token($type, $arguments, null, $closing);
                }
            }
        }
        $this->buildTree($tokens);
    }

    /**
     * Builds tree based on tokens
     * 
     * @param TypedArray<Token> $tokens
     * 
     * @return void
     */
    private function buildTree(TypedArray $tokens): void
    {
        $currentParent = new Token("page", "", null, false);
        $this->tokenTree = $currentParent;
        foreach ($tokens as $token) {
            if (in_array($token->getType(), $this->capturing)) {
                if ($token->isClosing() == false) {
                    $currentParent->addChild(
                        new Token(
                            $token->getType(),
                            $token->getArguments(),
                            $currentParent,
                            $token->isClosing())
                    );
                    $currentParent = $currentParent->getLastChild();
                } else {
                    $currentParent = $currentParent->getParent();
                }
            } else {
                $currentParent->addChild(
                    new Token(
                        $token->getType(),
                        $token->getArguments(),
                        $currentParent,
                        $token->isClosing()
                    )
                );
            }
        }
    }

    /**
     * Processes each token
     * 
     * @param Token $token
     */
    private function processTokens(Token $token): void
    {
        $tokenString = "{{{$token->getType()} {$token->getArguments()}}}";
        if ($token->getType() !== "page") {
            $this->tokenBehavior->{$token->getType()}($tokenString, $token->getArguments());
        }
        foreach ($token->getChildren() as $child) {
            $this->processTokens($child);
        }
    }

    /**
     * Cleans up closing tokens
     * 
     * @return void
     */
    private function cleanupEndTokens(): void
    {
        foreach ($this->capturing as $capture) {
            $this->template = str_replace("{{end $capture}}", "<?php } ?>", $this->template);
        }
    }

    /**
     * Minifies the translated template
     * 
     * @return void
     */
    private function minify(): void
    {
        $this->template = preg_replace("/\?\>\s+\<\?php/", "", $this->template);
        if ($this->minify) {
            $this->template = preg_replace("/\?>\s+<\?php/", "", $this->template);
            $this->template = preg_replace("/\n/", "", $this->template);
            $this->template = preg_replace("/(\s){2,}/", "$1", $this->template);
            $this->template = preg_replace("/\t/", "", $this->template);
            $this->template = preg_replace("/([a-zA-Z])\s+?\(/", "$1(", $this->template);
            $this->template = preg_replace("/and\(/", "and (", $this->template);
            $this->template = preg_replace("/;\s+?\}/", ";}", $this->template);
            $this->template = preg_replace("/\s+?\{/", "{", $this->template);
            $this->template = preg_replace("/\{\s([a-zA-Z])/", '{$1', $this->template);
            $this->template = preg_replace("/<!--.+?-->/", "", $this->template);
            $this->template = preg_replace("/>\s+?</", "><", $this->template);
        }
    }

    /**
     * Sets the default token functions up and registers aliases for them
     * 
     * @return void
     */
    private function setupTokenFunctions(): void
    {
        $this->tokenBehavior->register('dmsf.text', function (string $token, string $arguments) {
            if (strpos($arguments, '$') === false) {
                $text = $arguments;
            } else {
                $text = "{";
                $getParts = explode('.', $arguments);
                $text .= array_shift($getParts);
                foreach ($getParts as $get) {
                    $get = strtolower($get);
                    $get = ucfirst($get);
                    $text .= "->get{$get}()";
                }
                $text .= "}";
            }

            $this->template = str_replace($token, '<?php echo "' . $text . '"; ?>', $this->template);
        });
        $this->tokenBehavior->registerAlias('dmsf.text', 'text');
        $this->tokenBehavior->registerAlias('text', 't');

        $this->tokenBehavior->register('dmsf.foreach', function(string $token, string $arguments) {
            $foreachParameter = explode(",", $arguments);
            $parameterCount = count($foreachParameter);
            switch ($parameterCount) {
                case 1:
                    $foreachText = "<?php foreach (\${$foreachParameter[0]} as \$key => \$value) { ?>";
                    break;
                case 2: {
                    $arg = explode("=", $foreachParameter[1]);
                    $key = $arg[0] == "key" ? trim($arg[1]) : "key";
                    $value = $key == "key" ? trim($arg[1]) : "value";
                    $foreachText = "<?php foreach (\${$foreachParameter[0]} as \${$key} => \${$value}){ ?>";
                }
                break;
                case 3: {
                    $key = trim(explode("=", $foreachParameter[1])[1]);
                    $value = trim(explode("=", $foreachParameter[2])[1]);
                    $foreachText = "<?php foreach (\${$foreachParameter[0]} as \${$key} => \${$value}){ ?>";
                }
                break;
                default:
                    throw new \Exception("TOKEN_ARGUMENT_ERROR wrong amount of arguments for 'foreach', Expected 1 - 3, Got {$parameterCount}");
                    break;
            }
            $this->template = str_replace($token, $foreachText, $this->template);
        });
        $this->tokenBehavior->registerAlias('dmsf.foreach', 'foreach');

        $this->tokenBehavior->register('dmsf.range', function(string $token, string $arguments) {
            $delimiter = explode(",", $arguments);
            $this->template = str_replace($token, "<?php for(\$i = {$delimiter[0]}; \$i <= {$delimiter[1]}; \$i++) { ?>", $this->template);
        });
        $this->tokenBehavior->registerAlias('dmsf.range', 'range');

        $this->tokenBehavior->register('dmsf.if', function(string $token, string $arguments) {
            $this->template = str_replace($token, "<?php if ({$arguments}) { ?>", $this->template);
        });
        $this->tokenBehavior->registerAlias('dmsf.if', 'if');

        $this->tokenBehavior->register('dmsf.else', function(string $token, string $arguments) {
            $this->template = str_replace("{{else}}", "<?php } else { ?>", $this->template);
        });
        $this->tokenBehavior->registerAlias('dmsf.else', 'else');

        $this->tokenBehavior->register('dmsf.js', function (string $token, string $arguments) {
            $token = "{{js}}";
            $cwd = getcwd();
            $js = "cache/JavaScript/javascript.min.js";
            $this->template = str_replace($token, "{$js}", $this->template);
        });
        $this->tokenBehavior->registerAlias('dmsf.js', 'js');
    }

    public function setPage(string $pagename): TemplateEngine
    {
        $this->page = new Page($pagename);
        return $this;
    }

    public function getPageName(): string
    {
        return $this->page->getName();
    }
}
