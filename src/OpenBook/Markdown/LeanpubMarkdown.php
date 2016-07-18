<?php

namespace OpenBook\Markdown;

use cebe\markdown\Markdown;

/**
 * Markdown parser for leanpub flavored markdown.
 *
 * @author Oleg Krivtsov <olegkrivtsov@gmail.com>
 */
class LeanpubMarkdown extends Markdown 
{
    // include block element parsing using traits
    use \cebe\markdown\block\FencedCodeTrait;
    use \OpenBook\Markdown\Block\LeanpubFootnoteTrait;
    use \OpenBook\Markdown\Block\LeanpubHeadlineTrait;
    use \OpenBook\Markdown\Block\LeanpubQuoteTrait;
    use \OpenBook\Markdown\Block\LeanpubXmatterTrait;
    use \OpenBook\Markdown\Block\LeanpubTableTrait;

    // include inline element parsing using traits
    use \OpenBook\Markdown\Inline\FootnoteLinkTrait;
    use \OpenBook\Markdown\Inline\SuperscriptTrait;

    /**
     * Images
     * @var array 
     */
    public $images = [];

    /**
     * Table of contents
     * @var array 
     */
    public $toc;
    
    /**
     * True if current chapter belongs to mainmatter.
     * @var type 
     */
    public $isMainmatter;
    
    /**
     * Chapters.
     * @var type 
     */
    public $chapters = [];

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
    
    protected $curChapterNumber;
    
    protected $elementIds = [];
    
    protected $headlines = [];
    
    public function parse($text) 
    {
        $this->prepare();
        $this->chapters = [];
        $this->toc = '';
        $this->headlines = [];
        $this->images = [];
        $this->elementIds = [];
        $this->isMainmatter = false;
        $this->curChapterNumber = 0;
        
        if (empty($text)) {
            return '';
        }

        $text = str_replace(["\r\n", "\n\r", "\r"], "\n", $text);

        $this->prepareMarkers($text);

        $absy = $this->parseBlocks(explode("\n", $text));

        // Split absy into chapters
        $chapters = [];
        foreach ($absy as $block) {
            
            if ($block[0] == 'xmatter')
            {
                if ($block['type']=='mainmatter') {
                    $this->isMainmatter = true;
                } else {
                    $this->isMainmatter = false;
                }                
            }
            
            if ($block[0] == 'leanpubHeadline' && $block['level'] == 1) {
                // Add new chapter
                $chapterTitle = $this->renderAbsy($block['content']);
                $chapterId = preg_replace('/[^\w\d]/u', '_', $chapterTitle);
                $chapterId .= '.html';
                $chapters[] = [
                    'id' => $chapterId,
                    'title' => $chapterTitle,
                    'content' => []
                ];
                
                $this->curChapterId = $chapterId;
                
                if($this->isMainmatter)
                    $this->curChapterNumber++;
            }                       
            
            if (empty($chapters))
                continue;
                                    
            if ($block[0] == 'leanpubHeadline') {
                $level = $block['level'];                
                $block['chapterId'] = $this->curChapterId;
                $block['number'] = $this->_updateToc($block, $this->headlines, $level);
            }
            
            if (isset($block['id']))
                $this->elementIds[$block['id']] = $this->curChapterId;
            
            $chapters[count($chapters) - 1]['content'][] = $block;
        }
        
        // Render each chapter
        $markup = [];
        foreach ($chapters as $chapter) {
            $markup[] = [
                'id' => $chapter['id'],
                'title' => $chapter['title'],
                'content' => $this->renderAbsy($chapter['content'])
            ];
        }
        
        // Render Table of Contents
        $this->toc = $this->_renderToc($this->headlines);
        
        $this->cleanup();
        
        $this->chapters = $markup;
        
        return $markup;
    }

    protected function parseBlock($lines, $current) 
    {
        // Look for special properties before block start
        $props = $this->parsePropList($lines[$current]);
        if ($props != false && isset($lines[$current + 1]) && rtrim($lines[$current + 1]) != '') {
            $current++;
        }

        // identify block type for this line
        $blockType = $this->detectLineType($lines, $current);

        if ($blockType=='leanpubTable') {
            echo "a";
        }
        
        // call consume method for the detected block type to consume further lines
        $result = $this->{'consume' . $blockType}($lines, $current);

        if (is_array($props) && is_array($result)) {
            $result = [array_merge($result[0], $props), $result[1]];
        }
        
        $block = $result[0];        
                
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

        if (!isset($line[0]) || !$line[0] == '{' || substr($line, -1) != '}')
            return false;

        $line = substr($line, 1, strlen($line) - 2);

        $propNum = 0;
        for (;;) {

            // Read prop name
            if (!preg_match('/^([\w\d-_]+)\s*=/', $line, $matches))
                return false;

            $propName = $matches[1];

            if (strlen($propName) == 0)
                return false;

            $line = ltrim(substr($line, strlen($matches[0])));

            // Read prop value
            if (!isset($line[0]))
                return false;

            if ($line[0] == '"') {
                // Quoted value
                if (!preg_match('/^"(([^"]|\\")*)"\s*/', $line, $matches))
                    return false;

                $propVal = $matches[1];
                $line = ltrim(substr($line, strlen($matches[0])));
            } else if ($line[0] == "'") {
                // Quoted value
                if (!preg_match('/^\'(([^\']|\')*)\'\s*/', $line, $matches))
                    return false;

                $propVal = $matches[1];
                $line = ltrim(substr($line, strlen($matches[0])));
            } else {
                // Unquoted value
                if (!preg_match('/^([^\s,]*)\s*/', $line, $matches))
                    return false;

                $propVal = $matches[1];
                $line = ltrim(substr($line, strlen($matches[0])));
            }

            $props[$propName] = $propVal;

            // Skip comma
            if (preg_match('/\s*,\s*/', $line, $matches))
                $line = substr($line, strlen($matches[0]));

            $propNum++;

            if (strlen($line) == 0 || $propNum >= $maxPropCount)
                break;
        }

        return $props;
    }

    protected function _updateToc($block, &$headlines, $level, $depth=1, $number='')
    {
        if ($depth<$level) {
            $last = count($headlines)-1;
            if (!isset($headlines[$last]['children']))
                $headlines[$last]['children'] = [];
            
            if ($depth==1)
                $number = $this->curChapterNumber . '.';
            else
                $number .= count($headlines) . '.';
            
            $number = $this->_updateToc($block, $headlines[$last]['children'], $level, ++$depth, $number);           
            
        } else {
            if ($depth==1)
                $number = $this->curChapterNumber . '.';
            else
                $number .= count($headlines)+1 . '.';
            
            if ($this->isMainmatter)
                $block['number'] = $number;
            
            $headlines[] = $block;
        }
        
        if ($this->isMainmatter)
            return $number;
        
        return '';
    }
    
    protected function _renderToc($headlines) 
    {
        $toc = "<ul>\n";
        foreach ($headlines as $headline) {
            $toc .= $this->_renderTocHeadline($headline);
        }
        $toc .= "</ul>\n";
        
        return $toc;
    }

    protected function _renderTocHeadline($headline) 
    {
        $out = "<li>\n";
        $out .= "<a href=\"" . $headline['chapterId'] . ($headline['level']!=1?"#" 
             . $headline['id']:'') . '">' . (isset($headline['number'])?$headline['number']:'') . ' ' . $this->renderAbsy($headline['content']) . "</a>\n";
        if (isset($headline['children']) && count($headline['children'])!=0) {
            $out .= $this->_renderToc($headline['children']);            
        }
        $out .= "</li>\n";

        return $out;
    }

    protected function renderImage($block) 
    {
        if (substr($block['url'], 0, 7) != 'http://' && substr($block['url'], 0, 8) != 'https://') {
            $this->images[$block['url']] = $block['url'];
        }

        $out = "<div class=\"image-wrapper\">\n";
        $out .= "<a target=\"_blank\" href=\"" . $block['url'] . "\">\n";
        $out .= '<img src="' . htmlspecialchars($block['url'], ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"'
                . ' alt="' . htmlspecialchars($block['text'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"'
                . (empty($block['title']) ? '' : ' title="' . htmlspecialchars($block['title'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"')
                . ($this->html5 ? '>' : ' />');
        $out .= "</a>\n";
        if (isset($block['text']))
            $out .= "<div class=\"image-caption\">" . htmlspecialchars($block['text'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . "</div>\n";
        $out .= "</div>\n";

        return $out;
    }

    protected function renderCode($block) 
    {
        if (isset($block['lang']))
            $lang = 'language-' . $block['lang'];
        else
            $lang = 'language-text';

        if (isset($block['line-numbers']) && $block['line-numbers'] == 'on')
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
        if ($url[0] == '#' && isset($this->elementIds[substr($url, 1)])) {
            $block['url'] = $this->elementIds[substr($url, 1)] . $url;
        }

        return '<a href="' . htmlspecialchars($block['url'], ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"'
                . (empty($block['title']) ? '' : ' title="' . htmlspecialchars($block['title'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"')
                . '>' . $this->renderAbsy($block['text']) . '</a>';
    }
    
    protected function renderLeanpubHeadline($block)
    {
        $tag = 'h' . $block['level'];
        return "<$tag id=\"".$block['id']."\">" . (isset($block['number'])?$block['number']:'') . ' ' 
                . $this->renderAbsy($block['content']) . "</$tag>\n";
    }

}
