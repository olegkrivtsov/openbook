<?php
namespace OpenBook;

class PhpRenderer
{     
    public $vars=array();

    public function __get($name) 
    {        
        if (array_key_exists($name, $this->vars)) {
            return $this->vars[$name];
        } 
     }    
     
    public function render($file, $vars)
    {
        $this->vars = $vars;
        
        ob_start();
        include $file;
        return ob_get_clean();
     }
 }