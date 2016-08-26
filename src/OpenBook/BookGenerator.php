<?php
namespace OpenBook;

use OpenBook\Markdown\LeanpubMarkdown;
use OpenBook\SitemapGenerator;

class BookGenerator
{
    /**
     * List of warnings.
     * @var type 
     */
    protected $warnings = [];
    
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
     * PHP renderer.
     * @var type 
     */
    private $phpRenderer;
    
    /**
     * Markdown parser.
     * @var type 
     */
    private $markdownParser;
    
    /**
     * URLs for site map.
     * @var type 
     */
    private $siteUrls = [];
    
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->phpRenderer = new PhpRenderer();        
        $this->markdownParser = new LeanpubMarkdown();
    }
    
    /**
     * Returns the list of warnings.
     * @return type
     */
    public function getWarnings() 
    {
        return $this->warnings;
    }
    
    /**
     * Adds a message to log.
     */
    protected function log($msg)
    {
        echo $msg;
    }
    
    /**
     * Generates the book in HTML format. 
     */
    public function generate($dirName)
    {
        $this->warnings = [];
        
        // Append slash to dir name, if not exists.
        if(substr($dirName, -1)!='/')
            $dirName .= '/';
            
        // Check if directory exists.
        if(!is_dir($dirName))
            throw new \Exception("Passed argument is not a directory: $dirName\n");
        
        $this->log("Starting book generation\n");
        
        // Save directory name.
        $this->bookDir = $dirName;
        
        $this->outDir = $dirName . 'html/';
        
        // Read 'openbook.json' file.
        $this->getBookProps();
                
        // Parse manuscript files for each available language
        $this->processLanguages();
        
        // Generate index.html
        $this->generateIndex();
        
        // Generate sitemap.xml.
        $this->generateSiteMap();
        
        // Get list of asset files
        $themeAssetsDir = 'data/theme/default/assets';
        $assetFiles = $this->getDirContents($themeAssetsDir); 
        
        foreach ($assetFiles as $fileName) {
            $dstFileName = substr($fileName, strlen($themeAssetsDir));
            $dstFileName = $this->outDir . 'assets/' . $dstFileName;
            $this->filesToCopy[$fileName] = $dstFileName;
        }
        
        $faviconImage = $this->bookDir . 'manuscript/favicon.ico';
        if (is_readable($faviconImage)) {
            $this->filesToCopy[$faviconImage] = $this->outDir . 'favicon.ico';
        }
        
        // Copy asset files to output directory
        $this->copyFiles();
    }
    
    /**
     * Extracts book properties from openbook.json file.     
     */
    protected function getBookProps() 
    {
        $this->log("Reading openbook.json file\n");
        
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
        
        foreach ($languages as $langCode=>$langName)            
        {
            // Parse 'manuscript/Book.txt' file
            $manuscriptFiles = $this->parseBookFile($langCode);        
            
            // Parse manuscript files
            $mergedMarkdown = $this->mergeManuscriptFiles($langCode, $langName, $manuscriptFiles);
            
            // Generate chapters
            $this->generateChapters($langCode, $mergedMarkdown);
            
            // Generate toc.html
            $this->generateTableOfContents($langCode);            
        }
    }
    
    /**
     * Parses manuscript/Book.txt file. 
     */
    protected function parseBookFile($langCode)
    {
        $bookFile = $this->bookDir . "manuscript/$langCode/Book.txt";
        
        $this->log("Parsing book file $bookFile\n");
                        
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
        $this->log("Merging manuscript files for language '$langCode' ($langName)\n");
        
        $mergedContent = '';
        
        foreach ($manuscriptFiles as $fileName) {
            
            $filePath = $this->bookDir . 'manuscript/' . $langCode . '/' . $fileName;
            
            if(!is_readable($filePath)) 
                throw new \Exception("Could not read file $filePath");
            
            $this->log("Reading file: $filePath\n");
            
            $fileContent = file_get_contents($filePath);
            if($fileContent===false) {
                throw new \Exception("Could not open $filePath for reading.");
            }
            
            // Remove UTF-8 BOM
            $fileContentWithoutBOM = str_replace("\xEF\xBB\xBF", '', $fileContent);
            
            // Merge content
            $mergedContent .= $fileContentWithoutBOM . "\n\n";            
        }
        
        return $mergedContent;
    }
    
    /**
     * Generates chapter HTML files.
     */
    protected function generateChapters($langCode, $markdown)
    {
        $this->log("Generating chapters in HTML format for language $langCode\n");
        
        $upperAdContent = file_get_contents($this->bookDir . $this->bookProps['google_adsence']['chapter_upper_ad']);
        $lowerAdContent = file_get_contents($this->bookDir . $this->bookProps['google_adsence']['chapter_bottom_ad']);
        
        $this->markdownParser->parse($markdown);
        
        foreach ($this->markdownParser->warnings as $warning) {
            $this->log('Warning: '. $warning . "\n");
        }
        
        $this->warnings = array_merge($this->warnings, $this->markdownParser->warnings);
        
        // Generate an HTML file per chapter
        $outFiles = $this->markdownParser->outFiles;
        foreach ($outFiles as $idx=>$outFile) {
            
            $id = $outFile['id'];
            $isSection = strpos($id, '/')!=false;
            $title = $outFile['title'];
            $content = $outFile['content'];
    
            $linkPrev = null;
            if ($idx>0)
                $linkPrev = $outFiles[$idx-1]['id'];
            
            $linkNext = null;
            if ($idx<count($outFiles)-1)
                $linkNext = $outFiles[$idx+1]['id'];
            
            $vars = [
                'content' => $content,
                'linkPrev' => $linkPrev,
                'linkNext' => $linkNext,
                'upperAdContent' => $upperAdContent,
                'lowerAdContent' => $lowerAdContent,
                'bookProps' => $this->bookProps,
                'langCode' => $langCode,
                'dirPrefix' => $isSection?'../../':'../',
                'langDirPrefix' => $isSection?'../':'',
            ];

            $this->phpRenderer->clearVars();
            $content = $this->phpRenderer->render("data/theme/default/layout/chapter.php", $vars);

            $html = $this->renderMainLayout($content, $title, $isSection?'../../':'../', $langCode);
            
            $outFile = $this->outDir . $langCode . '/' . $id;
            
            $dirName = dirname($outFile);
            if (!is_dir($dirName)) {
                mkdir($dirName, '0775', true);
            }
            
            $this->log("Generating chapter: $outFile\n");
            
            file_put_contents($outFile, $html);
            
            $this->siteUrls[] = [$this->bookProps['book_website'] . '/' . $langCode . '/' . $id, 0.5];            
        }       
        
        // Add image files to be copied later
        foreach ($this->markdownParser->images as $fileName) {
            $srcFilePath = $this->bookDir . 'manuscript/' . $langCode . '/' . $fileName;
            $dstFilePath = $this->outDir . $langCode . '/' . $fileName;
            $this->filesToCopy[$srcFilePath] = $dstFilePath;
        }
    }
    
    /**
     * Generates toc.html file.
     */
    protected function generateTableOfContents($langCode)
    {
        // Generate toc.html
        
        $tocAdContent = file_get_contents($this->bookDir . $this->bookProps['google_adsence']['contents_ad']);
        
        $vars = [
            'languages' => $this->bookProps['languages'],
            'currentLanguage' => $langCode,
            'toc' => $this->markdownParser->toc,
            'bookProps' => $this->bookProps,
            'tocAdContent' => $tocAdContent
        ];
        
        $this->phpRenderer->clearVars();
        $content = $this->phpRenderer->render("data/theme/default/layout/toc.php", $vars);

        $html = $this->renderMainLayout($content, 'Table of Contents', '../', $langCode);
        
        $outFile = $this->outDir . $langCode . "/toc.html";
        
        $this->log("Generating table of contents file: $outFile\n");
        
        file_put_contents($outFile, $html);
        
        $this->siteUrls[] = [$this->bookProps['book_website'] . '/' . $langCode . '/toc.html', 1.0];
    }
    
    /**
     * Generates index.html file.
     */
    protected function generateIndex()
    {
        // Generate index.html
        
        $bookCoverImage = $this->bookDir . 'manuscript/title_page.png';
        if (!is_readable($bookCoverImage)) {
            $bookCoverImage = null;
        } else {
            $this->filesToCopy[$bookCoverImage] = $this->outDir . 'title_page.png';
            $bookCoverImage = 'title_page.png';
        }
        
        $vars = [
            'bookTitle' => $this->bookProps['book_title'],
            'bookCoverImage' => $bookCoverImage,
            'languages' => $this->bookProps['languages'],
            'bookProps' => $this->bookProps
        ];
        
        $this->phpRenderer->clearVars();
        $content = $this->phpRenderer->render("data/theme/default/layout/index.php", $vars);
        
        $html = $this->renderMainLayout($content, null);
        
        $outFile = $this->outDir . "index.html";
        
        $this->log("Generating index file: $outFile\n");
        
        file_put_contents($outFile, $html);
        
        array_unshift($this->siteUrls, [$this->bookProps['book_website'] . '/index.html', 1.0]);
    }
    
    /**
     * Generates the sitemap.xml file.
     */
    protected function generateSiteMap()
    {
        $this->log("Generating sitemap.xml\n");
        
        $baseURL = $this->bookProps['book_website'];
        $siteMapGenerator = new SitemapGenerator($baseURL, $this->outDir);
        
        foreach ($this->siteUrls as $urlInfo) {
            $siteMapGenerator->addUrl($urlInfo[0], date('c'), 'monthly', $urlInfo[1]);
        }
        
        $siteMapGenerator->createSitemap();
        $siteMapGenerator->writeSitemap();
    }
    
    /**
     * Renders main layout.
     */
    protected function renderMainLayout($content, $pageTitle, $dirPrefix = '', $langCode = 'en')
    {
        $vars = [
            'bookTitle' => $this->bookProps['book_title'],
            'bookSubtitle' => $this->bookProps['book_subtitle'],
            'keywords' => implode(',', $this->bookProps['keywords']),
            'pageTitle' => $pageTitle,
            'copyright' => $this->bookProps['copyright'],
            'links' => $this->bookProps['links'],
            'content' => $content,
            'dirPrefix' => $dirPrefix,
            'bookProps' => $this->bookProps,
            'langCode' => $langCode
        ];
                
        $html = $this->phpRenderer->render("data/theme/default/layout/main.php", $vars);
        
        return $html;
    }

    /**
     * Copies asset files to output directory.
     */
    protected function copyFiles()
    {
        $this->log("Copying files\n");
        
        $count = 0;
        foreach ($this->filesToCopy as $srcFile=>$dstFile) {
            if(!is_dir(dirname($dstFile)))
                mkdir(dirname($dstFile), '775', true);
            if(!is_readable($srcFile)) {
                $this->warnings[] = 'Failed to copy file: ' . $srcFile;
                $this->log('Failed to copy file: ' . $srcFile . "\n");
            } else if(copy($srcFile, $dstFile)) {
                $this->log("Copied file " . $srcFile . " to " . $dstFile . "\n");
                $count ++;
            }
        }
        
        $this->log("$count files copied.\n");
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

