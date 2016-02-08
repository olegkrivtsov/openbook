<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */
namespace OpenBook\Markdown\Block;

use Exception;

/**
 * Adds the leanpub-style quote blocks
 */
trait LeanpubQuoteTrait {
    
	protected $types = [
		'centeredparagraph' => 'C',
		'aside' => 'A',
		'warning' => 'W',
		'tip' => 'T',
		'error' => 'E',
		'information' => 'I',
		'question' => 'Q',
		'discussion' => 'D',
		'exercise' => 'X',
		'generic' => 'G',
	];
	    
	protected function identifyQuote($line) {
        
        foreach ($this->types as $token) {
            if(strlen($line)>=2 && $line[0] === $token && $line[1] === '>') {
                return true;
            }
        }
		
        return false;
	}
	
	protected function consumeQuote($lines, $current)
	{
        $types = '';
        foreach($this->types as $type) {
            $types .= $type;
        }
        
        $type = '';
        foreach ($this->types as $key=>$token) {
            if($lines[$current][0] === $token) {
                $type = $key;
                break;
            }
        }
        
		// consume until newline
		$content = [];
		for ($i = $current, $count = count($lines); $i < $count; $i++) {
			$line = $lines[$i];
			if (ltrim($line) !== '') {
				if(preg_match("/[$types]>/", $line, $matches)) {
                    $line = substr($line, strlen($matches[0]));
                }
				$content[] = $line;
			} else {
				break;
			}
		}
		$block = [
			'quote',
            'type' => $type,
			'content' => $this->parseBlocks($content),
			'simple' => true,
		];
		return [$block, $i];
	}
	
    protected function renderQuote($block)
	{
		return '<blockquote class="notquote ' . $block['type'] . '" data-type="' . 
                $block['type'] . '">' . $this->renderAbsy($block['content']) . '</blockquote>';
	}
	
}