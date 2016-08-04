<div class="language-selection">

    <?php 
    foreach ($this->languages as $firstLangCode=>$firstLangName) { break; }
    ?>
    
    <h1>Read this book in:</h1>
    
    <ul class="language-list">
    <?php foreach ($this->languages as $langCode=>$langTitle): ?>
        <li>
            <a href="<?= $langCode . '/toc.html' ?>"><?= $langTitle ?></a>
        </li>
    <?php endforeach; ?>
    </ul>

</div>

<div class="book-cover">
    <?php if(strlen($this->bookCoverImage)!=0): ?>
    <a href="<?= $firstLangCode . '/toc.html' ?>">
        <img id="book-cover" alt="<?= $this->bookTitle ?>" src="<?= $this->bookCoverImage ?>">
    </a>
    <?php endif; ?>
</div>
    
<div class="clear"></div>