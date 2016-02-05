<?php
/**
 * @copyright Copyright (c) 2016 Oleg Krivtsov
 */
namespace OpenBook\Markdown\Block;

/**
 * Adds the {frontmatter}, {mainmatter}, {backmatter} blocks
 */
trait LeanpubXmatterTrait {
    
    private $xmatterTypes = [
        'frontmatter',
        'mainmatter',
        'backmatter'
    ];
    
	protected function identifyXmatter($line)
	{
        foreach($this->xmatterTypes as $type) {
            if(preg_match("/^\{$type\}/", $line))
                return true;
        }
        return false;
	}
	/**
	 * Consume lines for a footnote
	 */
	protected function consumeXmatter($lines, $current)
	{
        foreach($this->xmatterTypes as $type) {
            if(preg_match("/^\{$type\}/", $lines[$current]))
                return [['xmatter', 'type'=>$type], $current];
        }
	}
	/**
	 * Render a xmatter
	 *
	 * @param $block
	 * @return string
	 */
	protected function renderXmatter($block)
	{
		return '';
	}
}