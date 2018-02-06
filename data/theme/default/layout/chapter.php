<!-- Navigation -->
<div class="navigation">
    <div class="prev-chapter">
    <?php if ($this->linkPrev): ?>    
        <a href="<?= $this->langDirPrefix ?><?= $this->linkPrev ?>">
            <img alt="Previous Chapter" src="<?= $this->dirPrefix ?>assets/images/left.png"><span class="nav-btn-text">Previous</span>
        </a>
    <?php endif; ?>
    </div>    
    <div class="contents">
        <a href="<?= $this->langDirPrefix ?>toc.html">
            <img alt="Contents" src="<?= $this->dirPrefix ?>assets/images/book.png"><span class="nav-btn-text">Contents</span>
        </a>
    </div>
    <div class="next-chapter">
        <?php if ($this->linkNext): ?>
        <a href="<?= $this->langDirPrefix ?><?= $this->linkNext ?>">
            <span class="nav-btn-text">Next</span><img alt="Next Chapter" src="<?= $this->dirPrefix ?>assets/images/right.png">
        </a>
        <?php endif; ?>
    </div>
    <?php if ($this->linkCurrentChapter): ?>
    <div class="current-chapter">
        <a href="<?= $this->langDirPrefix . $this->linkCurrentChapter ?>">
            <img alt="Contents" src="<?= $this->dirPrefix ?>assets/images/upload.png"><span class="nav-btn-text"><?= $this->currentChapterTitle ?></span>
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if ($this->bookProps['google_adsence']['enabled']): ?>
<?= $this->upperAdContent ?>
<?php endif; ?>

<!-- Chapter content -->
<div id="chapter_content">
<?php 
if (isset($this->bookProps['incomplete_translations']) && in_array($this->langCode, $this->bookProps['incomplete_translations'])): ?>
<div class="incomplete-translation">
    Translation into this language is not yet finished. You can help this project 
    by translating the chapters and contributing your changes.
</div>
<?php endif; ?>

<?php echo $this->content; ?>        
</div>

<?php if ($this->bookProps['google_adsence']['enabled']): ?>
<!-- Ads -->
<div id="ads-chapter-bottom">
<div>
<?= $this->lowerAdContent ?>
</div>
</div>
<?php endif; ?>
    
<!-- Navigation -->
<div class="navigation">
    <?php if ($this->linkCurrentChapter): ?>
    <div class="current-chapter">
        <a href="<?= $this->langDirPrefix . $this->linkCurrentChapter ?>">
            <img alt="Contents" src="<?= $this->dirPrefix ?>assets/images/upload.png"><span class="nav-btn-text"><?= $this->currentChapterTitle ?></span>
        </a>
    </div>
    <?php endif; ?>
    <div class="prev-chapter">
    <?php if ($this->linkPrev): ?>    
        <a href="<?= $this->langDirPrefix ?><?= $this->linkPrev ?>">
            <img alt="Previous Chapter" src="<?= $this->dirPrefix ?>assets/images/left.png"><span class="nav-btn-text">Previous</span>
        </a>
    <?php endif; ?>
    </div>    
    <div class="contents">
        <a href="<?= $this->langDirPrefix ?>toc.html">
            <img alt="Contents" src="<?= $this->dirPrefix ?>assets/images/book.png"><span class="nav-btn-text">Contents</span>
        </a>
    </div>
    <div class="next-chapter">
        <?php if ($this->linkNext): ?>
        <a href="<?= $this->langDirPrefix ?><?= $this->linkNext ?>">
            <span class="nav-btn-text">Next</span><img alt="Next Chapter" src="<?= $this->dirPrefix ?>assets/images/right.png">
        </a>
        <?php endif; ?>
    </div>        
</div>

<div id="disqus_thread"></div>

<?php
$this->externalStylesheets[] = 'assets/css/prism.css';
$this->externalScripts[] = 'assets/js/prism.js';
$pageTitle = $this->pageTitle;

if ($this->bookProps['disqus']['enabled']) {
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
}
?>
