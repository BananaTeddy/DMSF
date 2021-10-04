<?php declare(strict_types=1);
namespace system\Template;

final class Block {
    
    /** @var string $name */
    private $name;

    /** @var Block $var */
    private $parent = null;

    /** @var string $content */
    private $content;

    public function __construct(string $name, string $content, Block $parent = null)
    {
        $this->name = $name;
        $this->content = $content;
        $this->parent = $parent;
        if ($this->hasRecursion()) {
            throw new \Exception("BLOCK_RECURSION_ERROR: {$this->name}.tpl in {$this->parent->getName()} was already included");}
    }

    /**
     * Checks if there is recursion in our chain of blocks
     * 
     * @return bool
     **/
    private function hasRecursion(): bool
    {
        $block = $this;
        $parent = $block->getParent();
        while ($parent != null) {
            if ($this->name == $parent->getName()) {
                return true;
            }
            $parent = $parent->getParent();
        }
        return false;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParent(): ?Block
    {
        return $this->parent;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
