<?php
namespace OpenBook;

/**
 * This class can connect to a URL and check the HTTP response code. If response
 * is 200, the link is considered valid; otherwise an error is reported.
 */
class LinkValidator
{
    private $ch = null;
    
    public function __construct()
    {
        $this->ch = curl_init();
    }
    
    /**
     * Returns true if the link is valid, otherwise false.
     */
    public function validateLink($url, &$errorMsg)
    {
        $errorMsg = "Unspecified error";
        
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
        curl_setopt($this->ch, CURLOPT_USERAGENT, $agent);
        $cookie = tempnam ("/tmp", "CURLCOOKIE");
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($this->ch, CURLOPT_ENCODING, "");
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 20);
        
        $pageContent = curl_exec($this->ch);
        
        if ($pageContent==false) {
            $curlError = curl_error($this->ch);
            $errorMsg = "Error executing HTTP request to $url: $curlError";
            return false;
        }
        
        $info = curl_getinfo($this->ch);
        if ($info['http_code']!=200) {
            $errorMsg = "HTTP code for $url is invalid: " . $info['http_code'];
            return false;
        }
        
        $errorMsg = "The link $url is valid";
        return true;
    }
}
