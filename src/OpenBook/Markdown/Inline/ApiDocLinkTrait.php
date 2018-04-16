<?php
namespace OpenBook\Markdown\Inline;
/**
 * Adds API doc link elements
 */
trait ApiDocLinkTrait 
{
    /**
     * Parses a API doc link indicated by `@``.
     * @marker @`
     */
    protected function parseApiDocLink($text)
    {
        if (preg_match('/^@`([A-Za-z0-9\\\_]+)`(\[([A-Za-z0-9\\\_]+)\])?/', $text, $matches)) {

            if (isset($matches[3])) {
                return [
                        ['apiDocLink', $matches[1], $matches[3]],
                        strlen($matches[0])
                ];
            } else {
                return [
                        ['apiDocLink', $matches[1]],
                        strlen($matches[0])
                ];
            }
        }
        
        return [['text', $text[0]], 1];
    }

    protected function renderApiDocLink($block)
    {
        $displayedClassName = $block[1];
        $className = isset($block[2])?$block[2]:$displayedClassName;
        
        if ($className[0]=='\\')
            $className = substr($className, 1);
        
        $linkUrl = '#';
        
        if (!isset($this->apiIndex[$className])) {
            $this->warnings[] = 'Not found class or namespace name ' . $className . ' in API reference (in chapter ' . $this->curChapterId . ')';
        } else {
            $linkUrl = $this->apiIndex[$className];
            if (is_array($linkUrl)) {
                $this->warnings[] = 'Ambiguous class or namespace name ' . $className . ' in API reference (in chapter ' . $this->curChapterId . ')';
                $linkUrl = '#';
            }
        }
        
        $text = htmlspecialchars($block[1], ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return '<code><a href="' . $linkUrl . '" class="api-link">' . $text . '</a></code>';
    }
}

