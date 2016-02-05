<?php
namespace OpenBook;

class Application
{
    const STATUS_OK = 0;
    const STATUS_ERROR = 1;
    
    public function run($argc, $argv)
    {
        $status = self::STATUS_OK;
        
        try {
            //if($argc!=2)
            //    throw new \Exception ('Invalid argument count.');
        
            $dirName = '/home/oleg/share/using-zend-framework-3';//$argv[1];
            $generator = new \OpenBook\BookGenerator();
            $generator->generate($dirName);
                
        } catch (\Exception $ex) {
            $status = self::STATUS_ERROR;
            echo "Error: " . $ex->getMessage() . "\n";
        }
        
        if($status!=self::STATUS_OK)
            $this->printUsage();
        
        return $status; 
    }
    
    public function printUsage()
    {
        echo "Usage: php openbook.php <book_dir>\n";
    }
}
