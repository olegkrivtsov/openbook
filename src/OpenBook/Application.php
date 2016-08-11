<?php
namespace OpenBook;

class Application
{
    public function run($argc, $argv)
    {
        // Parse command-line arguments
        try {
            if($argc!=2)
                throw new \Exception('Invalid argument count passed.');
            
            $dirName = $argv[1];
            
        } catch (\Exception $ex) {
            $this->printUsage();
            echo "Error: " . $ex->getMessage() . "\n";
            return 1;
        }
        
        // Generate the book HTML
        try {
            
            $generator = new \OpenBook\BookGenerator();
            $generator->generate($dirName);
                
        } catch (\Exception $ex) {
            echo "Error: " . $ex->getMessage() . "\n";
            return 1;
        }
        
        $numWarnings = count($generator->getWarnings());
        
        echo "Done (0 errors; $numWarnings warnings)\n";
        return 0;
    }
    
    public function printUsage()
    {
        echo "Usage: php openbook.php <book_dir>\n";
    }
}
