<?php

/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace OpenBook\Markdown\Block;

/**
 * Adds the footnote blocks
 */
trait LeanpubFootnoteTrait 
{
    /**
     * identify a line as the beginning of a footnote block
     */
    protected function identifyLeanpubFootnote($line) 
    {
        return preg_match('/^\[\^(\w+?)\]:/', $line);
    }

    /**
     * Consume lines for a footnote
     */
    protected function consumeLeanpubFootnote($lines, $current) 
    {
        $id = '';
        $content = [];
        $count = count($lines);
        for ($i = $current; $i < $count; $i++) {
            $line = $lines[$i];
            
            if ($id=='') {
                if (preg_match('/^\[\^(\w+?)\]:[ \t]+/', $line, $matches)) {
                    $id = $matches[1];
                    $str = substr($line, strlen($matches[0]));
                    $content[] = $str;
                }
            } else if(strlen(trim($line))==0) {
                break;
            } else {
                $content[] = ltrim($line);
            }
        }
        
        $block = ['footnote', 'id'=>$id, 'content'=>$this->parseBlocks($content)];
                
        return [$block, $i];
    }

    /**
     * Render a footnote block
     *
     * @param $block
     * @return string
     */
    protected function renderFootnote($block) 
    {
        
    }    
}
