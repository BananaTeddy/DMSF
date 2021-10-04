<?php declare(strict_types=1);
namespace system\Template;

use system\Exceptions\TemplateNotFoundException;
use system\Helper\File;

final class Page {
    
    /** @var string|false $template */
    private $template;

    /** @var string $file */
    private $file;

    public function __construct(string $pagename)
    {
        $cwd = getcwd();
        $this->file = $pagename;
        if (File::Exists("templates/{$pagename}")) {
            $this->template = file_get_contents("{$cwd}/templates/{$pagename}");
        } else {
            throw new TemplateNotFoundException("Cannot find Template {$cwd}/templates/{$pagename}");
        }
    }

    /**
     * Returns template with all blocks resolved
     *
     * @return string
     **/
    public function getFullyResolvedTemplate(): string
    {
        return $this->resolveTemplate($this->template);
    }

    /**
     * Resolves part of a template
     *
     * @param string $partialTemplate
     * @param Block|null $prevBlock
     * 
     * @return string
     **/
    private function resolveTemplate(string $partialTemplate, Block $prevBlock = null): string
    {
        $cwd = getcwd();
        $pattern = "/\{\{block=(.+)\}\}/";
        preg_match_all($pattern, $partialTemplate, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (! file_exists("{$cwd}/templates/{$match[1]}.tpl")) {
                throw new \Exception("TEMPLATE_RESOLVE_ERROR: template file $match[1].tpl does not exist");
            }

            ob_start();
                include("{$cwd}/templates/{$match[1]}.tpl");
                $includedText = ob_get_contents();
                $block = new Block($match[1], $includedText, $prevBlock);
            ob_end_clean();

            if (preg_match($pattern, $includedText) === 1) {
                $partialTemplate = str_replace($match[0], $this->resolveTemplate($block->getContent(), $block), $partialTemplate);
            } else {
                $partialTemplate = str_replace($match[0], $includedText, $partialTemplate);
            }
        }
        return $partialTemplate;
    }

    public function getName(): string
    {
        return $this->file;
    }
}
