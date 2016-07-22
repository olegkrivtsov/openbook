<!-- Navigation -->
<div class="navigation">
    <div class="prev-chapter">
    <?php if ($this->linkPrev): ?>    
        <a href="<?= $this->linkPrev ?>">
            <img src="../assets/images/left.png"><span>Previous</span>
        </a>
    <?php endif; ?>
    </div>    
    <div class="contents">
        <a href="toc.html">
            <img src="../assets/images/book.png"><span>Contents</span>
        </a>
    </div>
    <div class="next-chapter">
        <?php if ($this->linkNext): ?>
        <a href="<?= $this->linkNext ?>">
            <span>Next</span><img src="../assets/images/right.png">
        </a>
        <?php endif; ?>
    </div>    
</div>

<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Using Zend Framework 3 Book - Chapter - Adaptive -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-3933778336056438"
     data-ad-slot="4531860323"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>

<!-- Chapter content -->
<div id="container">
<?php echo $this->content; ?>        
</div>

<!-- Ads -->
<div id="ads-chapter-bottom">
<div>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Using Zend Framework 3 Chapter - Bottom - Adaptive -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-3933778336056438"
     data-ad-slot="1546211126"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>
</div>
    
<!-- Navigation -->
<div class="navigation">
    <div class="prev-chapter">
    <?php if ($this->linkPrev): ?>    
        <a href="<?= $this->linkPrev ?>">
            <img src="../assets/images/left.png"><span>Previous</span>
        </a>
    <?php endif; ?>
    </div>    
    <div class="contents">
        <a href="toc.html">
            <img src="../assets/images/book.png"><span>Contents</span>
        </a>
    </div>
    <div class="next-chapter">
        <?php if ($this->linkNext): ?>
        <a href="<?= $this->linkNext ?>">
            <span>Next</span><img src="../assets/images/right.png">
        </a>
        <?php endif; ?>
    </div>    
</div>

<div id="disqus_thread"></div>

<script src="../assets/js/prism.js"></script>
<script>
var disqus_config = function () {
this.page.url = window.location.href; // Replace PAGE_URL with your page's canonical URL variable
this.page.identifier = '<?php echo $this->pageTitle; ?>'; // Replace PAGE_IDENTIFIER with your page's unique identifier variable
};

(function() { // DON'T EDIT BELOW THIS LINE
var d = document, s = d.createElement('script');

s.src = '//using-zend-framework-3-book.disqus.com/embed.js';

s.setAttribute('data-timestamp', +new Date());
(d.head || d.body).appendChild(s);
})();
</script>
<noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript" rel="nofollow">comments powered by Disqus.</a></noscript>

