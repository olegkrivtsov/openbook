<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace OpenBook\Markdown\Block;

/**
 * Adds the headline blocks
 */
trait LeanpubHeadlineTrait
{
    /**
     * identify a line as a headline
     */
    protected function identify9LeanpubHeadline($line, $lines, $current)
    {
        return ($line[0] === '#');
    }

    /**
     * Consume lines for a headline
     */
    protected function consume9LeanpubHeadline($lines, $current)
    {
        $line = $lines[$current];
        preg_match('/^(#+)/', $line, $matches);

        $level = strlen($matches[1]);
        if($level<1) $level = 1;
        if($level>6) $level = 6;

        $line = substr($line, $level);
        $line = trim($line);

        // Parse header id
        if(preg_match('/\{#([\w\d-_]+)\}$/', $line, $matches)) {
            $id = $matches[1];
            $line = substr($line, 0, strlen($line)-strlen($matches[0]));
        } else {
            $id = $this->renderAbsy($this->parseInline(trim($line)));
            $id = preg_replace('/[^\w\d]/u', '_', $id);
        }

        $block = [
            'leanpubHeadline',
            'content' => $this->parseInline(trim($line)),
            'level' => $level,
            'id' => $id
        ];

        return [$block, $current];
    }

    /**
     * Renders a headline
     */
    protected function renderLeanpubHeadline($block)
    {
        $tag = 'h' . $block['level'];
        return "<$tag id=\"".$block['id']."\">" . $this->renderAbsy($block['content']) . "</$tag>\n";
    }
}
