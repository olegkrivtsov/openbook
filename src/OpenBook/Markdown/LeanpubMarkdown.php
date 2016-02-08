<?php
namespace OpenBook\Markdown;
use cebe\markdown\Markdown;

/**
 * Markdown parser for leanpub flavored markdown.
 *
 * @author Oleg Krivtsov <olegkrivtsov@gmail.com>
 */
class LeanpubMarkdown extends Markdown {
    
    // include block element parsing using traits
    use \cebe\markdown\block\FencedCodeTrait;
    use \OpenBook\Markdown\Block\FootnoteTrait;
    use \OpenBook\Markdown\Block\LeanpubHeadlineTrait;
    use \OpenBook\Markdown\Block\LeanpubQuoteTrait;
    use \OpenBook\Markdown\Block\LeanpubXmatterTrait;
    use \OpenBook\Markdown\Block\LeanpubTableTrait;
    
    // include inline element parsing using traits
    use \OpenBook\Markdown\Inline\FootnoteLinkTrait;
    use \OpenBook\Markdown\Inline\SuperscriptTrait;
    
    public $splitIntoChapters = false;
    
    /**
     * Images
     * @var array 
     */
    public $images = [];
    
    /**
     * Table of contents
     * @var array 
     */
    public $toc = [];
        
    /**
	 * @inheritDoc
	 */
	protected $escapeCharacters = [
		// from Markdown
		'\\', // backslash
		'`', // backtick
		'*', // asterisk
		'_', // underscore
		'{', '}', // curly braces
		'[', ']', // square brackets
		'(', ')', // parentheses
		'#', // hash mark
		'+', // plus sign
		'-', // minus sign (hyphen)
		'.', // dot
		'!', // exclamation mark
		'<', '>',
		// added by LeanpubMarkdown
		':', // colon
		'|', // pipe
	];
    
    protected $curChapterId = '';
    
    protected $elementIds = [];
    
    protected $headlines = [];
    
    protected $curHeadlines = [-1, -1, -1, -1, -1, -1];
    
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
            
            // Split absy into chapters
            $chapters = [];
            foreach($absy as $block) {
                if($block[0]=='leanpubHeadline' && $block['level']==1) {
                    // Add new chapter
                    $chapterTitle = $this->renderAbsy($block['content']);
                    $chapterId = strtolower(preg_replace('/[^\w\d]/', '_', $chapterTitle));
                    $chapterId .= '.html';
                    $chapters[] = [
                        'id' => $chapterId,
                        'title' => $chapterTitle,
                        'content' => []
                    ];
                }
                
                if(empty($chapters))
                    continue;
                
                $chapters[count($chapters)-1]['content'][] = $block;
            }
            
            // Render each chapter
            $markup = [];
            foreach($chapters as $chapter) {
                $markup[] = [
                    'id' => $chapter['id'],
                    'title' => $chapter['title'],
                    'content' => $this->renderAbsy($chapter['content'])
                ];
            }
            
            // Render Table of Contents
            $this->_renderToc();
            
        } else {
            $markup = $this->renderAbsy($absy);
        }
        
		$this->cleanup();
		return $markup;
	}
    
    protected function parseBlock($lines, $current)
	{
        // Look for special properties before block start
        $props = $this->parsePropList($lines[$current]);
        if($props!=false && isset($lines[$current+1]) && rtrim($lines[$current+1])!='') {
            $current++;
        }
        
		// identify block type for this line
		$blockType = $this->detectLineType($lines, $current);

        // call consume method for the detected block type to consume further lines
		$result = $this->{'consume' . $blockType}($lines, $current);
        
        if(is_array($props) && is_array($result)) {
            $result= [array_merge($result[0], $props), $result[1]];
        }
        
        $block = $result[0];
        if($block[0]=='leanpubHeadline') {
            $level = $block['level'];
            
            if($level==1) {
                $this->headlines[] = $block;
                $this->curHeadlines = [count($this->headlines)-1, -1, -1, -1, -1, -1];
            } else if($level==2) {
                
            }
        }
        
        return $result;
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
            } else if($line[0]=="'") {
                // Quoted value
                if(!preg_match('/^\'(([^\']|\')*)\'\s*/', $line, $matches))
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
    
    protected function _renderToc() {
        
        $toc = "<ul>\n";
        foreach ($this->headlines as $headline) {
            $toc .= $this->_renderTocHeadline($headline);
        }
        $toc .= "</ul>\n";
        $this->toc = $toc;
    }
    
    protected function _renderTocHeadline($headline)
    {
        $out = "<li>\n";
        $out .= "<a href=\"#".$headline['id'].'">' . $this->renderAbsy($headline['content']) . "</a>\n";
        if(isset($headline['children'])) {
            foreach($headline['children'] as $childHeadline) {
                $out .= $this->_renderTocHeadline($childHeadline);
            }
        }
        $out .= "</li>\n";
        
        return $out;
    }
    
    protected function renderImage($block)
	{
        if(substr($block['url'], 0, 7)!='http://' && substr($block['url'], 0, 8)!='https://') {
            $this->images[$block['url']] = $block['url'];
        }
        
		$out = "<div class=\"image-wrapper\">\n";
        $out .= "<a target=\"_blank\" href=\"".$block['url']."\">\n";
        $out .= '<img src="' . htmlspecialchars($block['url'], ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"'
			. ' alt="' . htmlspecialchars($block['text'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"'
			. (empty($block['title']) ? '' : ' title="' . htmlspecialchars($block['title'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"')
			. ($this->html5 ? '>' : ' />');
        $out .= "</a>\n";
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
    
    protected function renderLink($block)
	{
		if (isset($block['refkey'])) {
			if (($ref = $this->lookupReference($block['refkey'])) !== false) {
				$block = array_merge($block, $ref);
			} else {
				return $block['orig'];
			}
		}
        
        $url = $block['url'];
        if($url[0]=='#' && isset($this->elementIds[substr($url, 1)])) {
            $block['url'] = $this->elementIds[substr($url, 1)] . $url;
        }
        
		return '<a href="' . htmlspecialchars($block['url'], ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"'
			. (empty($block['title']) ? '' : ' title="' . htmlspecialchars($block['title'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"')
			. '>' . $this->renderAbsy($block['text']) . '</a>';
	}
}