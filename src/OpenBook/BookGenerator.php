<?php
namespace OpenBook;

use OpenBook\Markdown\LeanpubMarkdown;

class BookGenerator
{
    /**
     * Directory where book files are located.
     * @var type 
     */
    protected $bookDir;
    
    /**
     * Name of the directory where output files will be written.
     * @var type 
     */
    protected $outDir;
    
    /**
     * Array containing book properties extracted from openbook.json file.
     * @var type 
     */
    protected $bookProps = [];
            
    /**
     * The list of asset files to be copied to output directory.
     * @var type 
     */
    protected $filesToCopy = [];
                
    /**
     * Generates the book in HTML format. 
     */
    public function generate($dirName)
    {
        // Append slash to dir name, if not exists.
        if(substr($dirName, -1)!='/')
            $dirName .= '/';
            
        // Check if directory exists.
        if(!is_dir($dirName))
            throw new \Exception("Passed argument is not a directory: $dirName\n");
        
        echo "Starting book generation\n";
        
        // Save directory name.
        $this->bookDir = $dirName;
        
        $this->outDir = $dirName . 'html/';
        
        // Read 'openbook.json' file.
        $this->getBookProps();
                
        // Parse manuscript files for each available language
        $this->processLanguages();
        
        // Get list of asset files
        $themeAssetsDir = 'data/theme/default/assets';
        $assetFiles = $this->getDirContents($themeAssetsDir); 
        
        foreach ($assetFiles as $fileName) {
            $dstFileName = substr($fileName, strlen($themeAssetsDir));
            $dstFileName = $this->outDir . 'assets/' . $dstFileName;
            $this->filesToCopy[] = [$fileName, $dstFileName];
        }
        
        // Copy asset files to output directory
        $this->copyFiles();
    }
    
    /**
     * Extracts book properties from openbook.json file.     
     */
    protected function getBookProps() 
    {
        echo "Reading openbook.json file\n";
        
        $fileName = $this->bookDir . 'openbook.json';
        
        if(!is_readable($fileName))
            throw new \Exception("The file $fileName doesn't exist or is not readable.");
        
        $json = file_get_contents($fileName);
        
        // Remove UTF-8 BOM
        $json = str_replace("\xEF\xBB\xBF",'', $json);
        
        $bookProps = json_decode($json, true);
        
        if(!is_array($bookProps))
            throw new \Exception("The file '$fileName' is not in JSON format.");
        
        $this->bookProps = $bookProps;
    }
                
    /**
     * Walks through available languages and parses chapters for each language.
     */
    protected function processLanguages()
    {
        if(!isset($this->bookProps['languages']))
            throw new \Exception("Not found languages section in openbook.json file");
        
        $languages = $this->bookProps['languages'];
        
        foreach ($languages as $langStr)            
        {
            $parts = explode(':', $langStr);
            $langCode = trim($parts[0]);
            $langName = trim($parts[1]);
            
            // Parse 'manuscript/Book.txt' file
            $manuscriptFiles = $this->parseBookFile($langCode);        
            
            // Parse manuscript files
            $mergedMarkdown = $this->mergeManuscriptFiles($langCode, $langName, $manuscriptFiles);
            
            // Generate index.html
            $this->generateIndex();
            
            // Generate chapters
            $this->generateChapters($langCode, $mergedMarkdown);
        }
    }
    
    /**
     * Parses manuscript/Book.txt file. 
     */
    protected function parseBookFile($langCode)
    {
        $bookFile = $this->bookDir . "manuscript/$langCode/Book.txt";
        
        echo "Parsing book file $bookFile\n";
                        
        if(!is_readable($bookFile))
            throw new \Exception("$bookFile doesn't exist or is not readable.");
        
        $str = file_get_contents($bookFile);
        if($str===false)
            throw new \Exception("Could not open $bookFile for reading.");
        
        // Remove UTF-8 BOM
        $str = str_replace("\xEF\xBB\xBF",'', $str);
        
        $fileNames = array_filter(explode("\n", $str));
        
        $manuscriptFiles = [];
        foreach ($fileNames as $fileName) {
            $manuscriptFiles[] = trim($fileName);
        }
        
        return $manuscriptFiles;
    }
    
    /**
     * Merges manuscript files for the given language into a single string.
     */
    protected function mergeManuscriptFiles($langCode, $langName, $manuscriptFiles)
    {
        echo "Merging manuscript files for language '$langCode' ($langName)\n";
        
        $mergedContent = '';
        
        foreach ($manuscriptFiles as $fileName) {
            
            $filePath = $this->bookDir . 'manuscript/' . $langCode . '/' . $fileName;
            
            if(!is_readable($filePath)) 
                throw new \Exception("Could not read file $filePath");
            
            echo "Reading file: $filePath\n";
            
            $fileContent = file_get_contents($filePath);
            if($fileContent===false) {
                throw new \Exception("Could not open $filePath for reading.");
            }
            
            // Remove UTF-8 BOM
            $fileContentWithoutBOM = str_replace("\xEF\xBB\xBF", '', $fileContent);
            
            // Merge content
            $mergedContent .= $fileContentWithoutBOM;            
        }
        
        return $mergedContent;
    }
    
    /**
     * Generates chapter HTML files.
     */
    protected function generateChapters($langCode, $markdown)
    {
        echo "Generating chapters in HTML format for language $langCode\n";
        
        $parser = new LeanpubMarkdown();
        
        $parser->parse($markdown);
        
        // Generate an HTML file per chapter
        foreach ($parser->chapters as $chapter) {
            
            $chapterId = $chapter['id'];
            $chapterTitle = $chapter['title'];

            echo "Generating chapter: $chapterId\n";

            $chapterContent = $parser->render($chapter['content']);

            $phpRenderer = new PhpRenderer();

            $vars = [
                'bookTitle' => $this->bookProps['book_title'],
                'pageTitle' => $chapterTitle,
                'copyright' => $this->bookProps['copyright'],
                'content' => $chapterContent
            ];

            $html = $phpRenderer->render("data/theme/default/layout/chapter.php", $vars);

            if (!is_dir($this->outDir . $langCode)) {
                mkdir($this->outDir . $langCode, '0775', true);
            }

            $outFile = $this->outDir . $langCode . '/' . $chapterId;
            file_put_contents($outFile, $html);
        }       
    }
    
    /**
     * Generates index.html file.
     */
    protected function generateIndex()
    {
        echo "Generating index.html\n";
        
        // Generate index.html
        $vars = [
            'bookTitle' => $this->bookProps['book_title'],
            'pageTitle' => $this->bookProps['book_title'],            
            'content' => ''
        ];

        $html = $phpRenderer->render("data/theme/default/layout/index.php", $vars);
        $outFile = $this->outDir . "index.html";
        file_put_contents($outFile, $html);
    }
    
    /**
     * Copies asset files to output directory.
     */
    protected function copyFiles()
    {
        echo "Copying files\n";
        
        $count = 0;
        foreach ($this->filesToCopy as $file) {
            if(!is_dir(dirname($file[1])))
                mkdir(dirname($file[1]), '775', true);
            if(!is_readable($file[0])) {
                echo 'Error copying file: ' . $file[0] . "\n";
            } else if(copy($file[0], $file[1])) {
                echo "Copied file: " . $file[0] ."\n";
                $count ++;
            }
        }
        
        echo "$count files copied.\n";
    }
    
    /**
     * Recursively scans directory for files and subdirectories. 
     */
    private function getDirContents($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach($files as $key => $value){
            $path = $dir.DIRECTORY_SEPARATOR.$value;
            if(!is_dir($path)) {
                $results[] = $path;
            } else if($value != "." && $value != "..") {
                $this->getDirContents($path, $results);                
            }
        }

        return $results;
    }
}

