<?php
namespace OpenBook\Markdown;
use cebe\markdown\MarkdownExtra;

/**
 * Markdown parser for leanpub flavored markdown.
 *
 * @author Oleg Krivtsov <olegkrivtsov@gmail.com>
 */
class LeanpubMarkdown extends MarkdownExtra {
    
    // include block element parsing using traits
    use \cebe\markdown\block\FencedCodeTrait;
    use \OpenBook\Markdown\Block\FootnoteTrait;
    use \OpenBook\Markdown\Block\LeanpubQuoteTrait;
    use \OpenBook\Markdown\Block\LeanpubXmatterTrait;
    use \OpenBook\Markdown\Block\LeanpubTableTrait;
    
    // include inline element parsing using traits
    use \OpenBook\Markdown\Inline\FootnoteLinkTrait;
    use \OpenBook\Markdown\Inline\SuperscriptTrait;
    
    public $splitIntoChapters = false;
    
    public $images = [];
    
    public function parse($text)
	{
		$this->prepare();
		
		if (empty($text)) {
			return '';
		}

		$text = str_replace(["\r\n", "\n\r", "\r"], "\n", $text);

		$this->prepareMarkers($text);

		$absy = $this->parseBlocks(explode("\n", $text));
		
        if($this->splitIntoChapters) {
            $markup = [];
            foreach($absy as $chapterAbsy) {
                $chapterId = $this->renderAbsy($chapterAbsy['id']);
                $chapterMarkup = $this->renderAbsy($chapterAbsy['content']);
                $markup[] = [
                    'id'=>$chapterId, 
                    'content'=>$chapterMarkup
                ];
            }
        } else {
            $markup = $this->renderAbsy($absy);
        }
        
		$this->cleanup();
		return $markup;
	}
    
    protected function parseBlocks($lines)
	{
		if ($this->_depth >= $this->maximumNestingLevel) {
			// maximum depth is reached, do not parse input
			return [['text', implode("\n", $lines)]];
		}
		$this->_depth++;

		$blocks = [];

		$blockTypes = $this->blockTypes();

		// convert lines to blocks
		for ($i = 0, $count = count($lines); $i < $count; $i++) {
			$line = $lines[$i];
			if (!empty($line) && rtrim($line) !== '') { // skip empty lines
                
                // Look for special properties before block start
                $props = $this->parsePropList($lines[$i]);
                if($props!=false && isset($lines[$i+1]) && rtrim($lines[$i+1])!='') {
                    $i++;
                    $line = $lines[$i];
                }
                    
				// identify a blocks beginning
				$identified = false;
				foreach($blockTypes as $blockType) {
					if ($this->{'identify' . $blockType}($line, $lines, $i)) {
						// call consume method for the detected block type to consume further lines
						list($block, $i) = $this->{'consume' . $blockType}($lines, $i);
						if ($block !== false) {
                            if(is_array($props))
                                $block = array_merge($block, $props);
                            
                            if($this->_depth==1 && $this->splitIntoChapters) {
                                if($block[0]=='headline' && $block['level']==1) {
                                    // New chapter
                                    $blocks[] = [
                                        'id'=> $block['content'], 
                                        'content'=>[]
                                    ];
                                }
                                // If there is no chapters yet, ignore this block
                                if(!empty($blocks)) {
                                    // Insert this block into last chapter    
                                    $blocks[count($blocks)-1]['content'][] = $block;
                                }
                                
                            } else {
                                $blocks[] = $block;
                            }
						}
						$identified = true;
						break 1;
					}
				}
				// consider the line a normal paragraph
				if (!$identified) {
					list($block, $i) = $this->consumeParagraph($lines, $i);
					if($this->_depth==1 && $this->splitIntoChapters) {
                        
                        // If there is no chapters yet, ignore this block
                        if(!empty($blocks)) {
                            // Insert this block into last chapter    
                            $blocks[count($blocks)-1]['content'][] = $block;
                        }

                    } else {
                        $blocks[] = $block;
                    }
				}
			}
		}

		$this->_depth--;

		return $blocks;
	}
    
    /**
     * Parses the property list like {line-numbers=on, lang=php}
     * @param type $line
     */
    public function parsePropList($line)
    {
        $props = [];
        $maxPropCount = 32;
        
        $line = rtrim($line);
        
        if(!isset($line[0]) || !$line[0]=='{' || substr($line, -1)!='}')
            return false;
        
        $line = substr($line, 1, strlen($line)-2);
        
        $propNum = 0;
        for (;;) {
            
            // Read prop name
            if(!preg_match('/^([\w\d-_]+)\s*=/', $line, $matches))
                return false;
            
            $propName = $matches[1];
            
            if(strlen($propName)==0)
                return false;
            
            $line = ltrim(substr($line, strlen($matches[0])));
            
            // Read prop value
            if(!isset($line[0]))
                return false;
            
            if($line[0]=='"') {
                // Quoted value
                if(!preg_match('/^"(([^"]|\\")*)"\s*/', $line, $matches))
                    return false;
                
                $propVal = $matches[1];                
                $line = ltrim(substr($line, strlen($matches[0])));
            } else {
                // Unquoted value
                if(!preg_match('/^([^\s,]*)\s*/', $line, $matches))
                    return false;
                
                $propVal = $matches[1];                
                $line = ltrim(substr($line, strlen($matches[0])));
            }
            
            $props[$propName] = $propVal;
            
            // Skip comma
            if(preg_match('/\s*,\s*/', $line, $matches))
                $line = substr($line, strlen($matches[0]));
            
            $propNum++;
            
            if(strlen($line)==0 || $propNum>=$maxPropCount)
                break;
        }
        
        return $props;
    }
    
    protected function renderImage($block)
	{
        if(substr($block['url'], 0, 7)!='http://' && substr($block['url'], 0, 8)!='https://') {
            $this->images[$block['url']] = $block['url'];
        }
        
		$out = "<div class=\"image-wrapper\">\n";
        $out .= '<img src="' . htmlspecialchars($block['url'], ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"'
			. ' alt="' . htmlspecialchars($block['text'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"'
			. (empty($block['title']) ? '' : ' title="' . htmlspecialchars($block['title'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"')
			. ($this->html5 ? '>' : ' />');
        if(isset($block['text']))
            $out .= "<div class=\"image-caption\">".htmlspecialchars($block['text'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8')."</div>\n";
        $out .= "</div>\n";
        
        return $out;
	}
    
    protected function renderCode($block)
	{
        if(isset($block['lang']))
            $lang = 'language-' . $block['lang'];
        else 
            $lang = '';
        
        if(isset($block['line-numbers']) && $block['line-numbers']=='on')
            $linenumbers = 'line-numbers';
        else 
            $linenumbers = '';
        
        
		return ("<pre class=\"$linenumbers\"><code class=\"$lang\">")
			. htmlspecialchars($block['content'] . "\n", ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8')
			. "</code></pre>\n";
	}
}