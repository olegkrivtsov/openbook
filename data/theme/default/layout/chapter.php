<!-- Navigation -->
<div class="navigation">
    <div class="prev-chapter">
    <?php if ($this->linkPrev): ?>    
        <a href="<?= $this->linkPrev ?>">
            <img alt="Previous Chapter" src="../assets/images/left.png"><span>Previous</span>
        </a>
    <?php endif; ?>
    </div>    
    <div class="contents">
        <a href="toc.html">
            <img alt="Contents" src="../assets/images/book.png"><span>Contents</span>
        </a>
    </div>
    <div class="next-chapter">
        <?php if ($this->linkNext): ?>
        <a href="<?= $this->linkNext ?>">
            <span>Next</span><img alt="Next Chapter" src="../assets/images/right.png">
        </a>
        <?php endif; ?>
    </div>    
</div>

<?= $this->upperAdContent ?>

<!-- Chapter content -->
<div id="chapter_content">
<?php echo $this->content; ?>        
</div>

<!-- Ads -->
<div id="ads-chapter-bottom">
<div>
<?= $this->lowerAdContent ?>
</div>
</div>
    
<!-- Navigation -->
<div class="navigation">
    <div class="prev-chapter">
    <?php if ($this->linkPrev): ?>    
        <a href="<?= $this->linkPrev ?>">
            <img alt="Previous Chapter" src="../assets/images/left.png"><span>Previous</span>
        </a>
    <?php endif; ?>
    </div>    
    <div class="contents">
        <a href="toc.html">
            <img alt="Contents" src="../assets/images/book.png"><span>Contents</span>
        </a>
    </div>
    <div class="next-chapter">
        <?php if ($this->linkNext): ?>
        <a href="<?= $this->linkNext ?>">
            <span>Next</span><img alt="Next Chapter" src="../assets/images/right.png">
        </a>
        <?php endif; ?>
    </div>    
</div>

<div id="disqus_thread"></div>

<?php
$this->externalStylesheets[] = 'assets/css/prism.css';
$this->externalScripts[] = 'assets/js/prism.js';
$pageTitle = $this->pageTitle;
$disqusSrc = $this->bookProps['disqus']['src'];
$this->inlineScripts[] = <<<EOT
<script>
var disqus_config = function () {
this.page.url = window.location.href; // Replace PAGE_URL with your page's canonical URL variable
this.page.identifier = ''; // Replace PAGE_IDENTIFIER with your page's unique identifier variable
};

(function() { // DON'T EDIT BELOW THIS LINE
var d = document, s = d.createElement('script');

s.src = '$disqusSrc';

s.setAttribute('data-timestamp', +new Date());
(d.head || d.body).appendChild(s);
})();
</script>
<noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript" rel="nofollow">comments powered by Disqus.</a></noscript>
EOT;
?>
