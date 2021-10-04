<?php declare(strict_types=1);
namespace system\Template;

use system\Helper\TypedArray\TypedArray;

final class Token {
    
    /** @var string $type */
    private $type;

    /** @var string $arguments */
    private $arguments;

    /** @var bool $closing */
    private $closing;

    /** @var Token $parent */
    private $parent = null;

    /** @var TypedArray<Token> $children */
    private $children = null;

    public function __construct(string $type, string $arguments, Token $parent = null, bool $closing = false) {
        $this->type = $type;
        $this->arguments = $arguments;
        $this->closing = $closing;
        $this->parent = $parent;
        $this->children = new TypedArray(Token::class);
    }

    /**
     * Adds a child to the token
     *
     * @param Token $child the child to be added
     * @return void
     **/
    public function addChild(Token $child)
    {
        $this->children[] = $child;
    }

    public function getType(): string
    {
        return $this->type;
    }
    
    public function getArguments(): string
    {
        return $this->arguments;
    }
    
    public function isClosing(): bool
    {
        return $this->closing;
    }
    
    public function getParent(): Token
    {
        return $this->parent;
    }
    
    /** @return TypedArray<Token> */
    public function getChildren(): TypedArray
    {
        return $this->children;
    }
    
    public function getFirstChild(): ?Token
    {
        return $this->children->toArray()[0] ?? null;
    }
    
    public function getLastChild(): ?Token
    {
        $arr = $this->children->toArray();
        return end($arr) ?? null;
    }
    
}
