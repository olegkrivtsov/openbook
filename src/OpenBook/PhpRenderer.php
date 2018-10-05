<?php
namespace OpenBook;

class PhpRenderer
{     
    public $vars= [];

    public $externalStylesheets = [];
    
    public $externalScripts = [];
    
    public $inlineScripts = [];
    
    public function __get($name) 
    {        
        if (array_key_exists($name, $this->vars)) {
            return $this->vars[$name];
        } 
    }    

    public function clearVars()
    {
        $this->vars = [];
        $this->externalScripts = [];
        $this->inlineScripts = [];
        $this->externalStylesheets = [];
    }
    
    public function render($file, $vars)
    {
        $this->vars = $vars;
        
        ob_start();
        include $file;
        return ob_get_clean();
    }
    
    public function escapeHtml($str) 
    {
        return htmlentities($str, ENT_COMPAT | ENT_HTML401, 'UTF-8');
    }
 }