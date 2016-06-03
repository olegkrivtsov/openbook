<?php
namespace OpenBook;

use OpenBook\Markdown\LeanpubMarkdown;

class BookGenerator
{
    protected $bookDir;
    
    protected $bookProps = [];
    
    protected $filesToCopy = [];
                
    public function generate($dirName)
    {
        if(substr($dirName, -1)!='/')
            $dirName .= '/';
            
        if(!is_dir($dirName))
            throw new \Exception("Passed argument is not a directory: $dirName\n");
        
        $this->bookDir = $dirName;
        
        $this->getBookProps();
        
        $this->parseBookFile();
    }
    
    protected function getBookProps() 
    {
        $fileName = $this->bookDir . 'openbook.json';
        $json = file_get_contents($fileName);
        
        $bookProps = json_decode($json, true);
        if(!is_array($bookProps))
            throw new \Exception("$fileName is not in JSON format");
        
        $this->bookProps = $bookProps;
    }
    
    protected function parseBookFile()
    {
        $bookFile = $this->bookDir . 'manuscript/Book.txt';
        
        if(!is_readable($bookFile))
            throw new \Exception("$bookFile doesn't exist or is not readable\n");
        
        $str = file_get_contents($bookFile);
        if($str===false)
            throw new \Exception("Could not open $bookFile for reading\n");
        
        $chapters = explode("\n", $str);
        
        $mergedContent = '';
        
        foreach ($chapters as $chapterFile) {
            
            $chapterFile = $this->bookDir . 'manuscript/' . trim($chapterFile);
            
            if(!is_readable($chapterFile))
                throw new \Exception("$chapterFile doesn't exist or is not readable\n");
            
            $chapterContent = file_get_contents($chapterFile);
            if($str===false)
                throw new \Exception("Could not open $chapterFile for reading\n");
            
            // Remove BOM
            $chapterContent = str_replace("\xEF\xBB\xBF",'', $chapterContent);
            
            $mergedContent .= $chapterContent;
        }            
        
        $splitIntoChapters = true;
        
        $parser = new LeanpubMarkdown();
        $parser->splitIntoChapters = $splitIntoChapters;
        $data = $parser->parse($mergedContent);
        
        $this->filesToCopy = [
            [__DIR__ . "/../../data/css/style.css", $this->bookDir . 'preview/css/style.css'],
            [__DIR__ . "/../../data/js/prism.js", $this->bookDir . 'preview/js/prism.js'],
            [__DIR__ . "/../../data/css/prism.css", $this->bookDir . 'preview/css/prism.css'],
            [__DIR__ . "/../../data/images/info.png", $this->bookDir . 'preview/images/info.png'],
            [__DIR__ . "/../../data/images/question.png", $this->bookDir . 'preview/images/question.png'],
            [__DIR__ . "/../../data/images/tip.png", $this->bookDir . 'preview/images/tip.png'],
            [__DIR__ . "/../../data/images/left.png", $this->bookDir . 'preview/images/left.png'],
            [__DIR__ . "/../../data/images/right.png", $this->bookDir . 'preview/images/right.png'],
            [__DIR__ . "/../../data/images/book.png", $this->bookDir . 'preview/images/book.png'],
        ];
        
        $images = $parser->images;
        foreach ($images as $imageFile) {
            $srcPath = $this->bookDir . 'manuscript/' . $imageFile;
            $dstPath = $this->bookDir . 'preview/' . $imageFile;
            $this->filesToCopy[] = [$srcPath, $dstPath];
        }
        
        if ($splitIntoChapters) {
            
            foreach ($data as $chapter) {
                $chapterId = $chapter['id'];
                $chapterTitle = $chapter['title'];
                $chapterContent = $chapter['content'];
                
                $phpRenderer = new PhpRenderer();
                
                $vars = [
                    'bookTitle' => $this->bookProps['book_title'],
                    'pageTitle' => $chapterTitle,
                    'content' => $chapterContent
                ];
                
                $html = $phpRenderer->render(__DIR__ . "/../../data/layout/chapter.php", $vars);
                $outFile = $this->bookDir . "preview/$chapterId";
                file_put_contents($outFile, $html);
                
                $vars = [
                    'bookTitle' => $this->bookProps['book_title'],
                    'pageTitle' => $this->bookProps['book_title'],
                    'content' => $parser->toc
                ];
                
                $html = $phpRenderer->render(__DIR__ . "/../../data/layout/index.php", $vars);
                $outFile = $this->bookDir . "preview/index.html";
                file_put_contents($outFile, $html);
            }
            
        } else {
            $outFile = $this->bookDir . 'preview/index.html';
            file_put_contents($outFile, $html);
        }
        
        $this->copyFiles();
    }
    
    protected function copyFiles()
    {
        echo "Copying files...\n";
        
        $count = 0;
        foreach ($this->filesToCopy as $file) {
            if(!is_dir(dirname($file[1])))
                mkdir(dirname($file[1]), '775', true);
            if(!is_readable($file[0])) {
                echo 'Error copying file: ' . $file[0] . "\n";
            } else if(copy($file[0], $file[1])) {
                $count ++;
            }
        }
        
        echo "$count files copied.\n";
    }
}

