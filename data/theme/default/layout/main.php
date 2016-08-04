<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<meta name="description" content="<?= $this->bookSubtitle ?>">
<meta name="keywords" content="<?= $this->keywords ?>">
<meta name="author" content="<?= $this->copyright ?>">
<meta name=viewport content="width=device-width, initial-scale=1">
<link href="<?= $this->dirPrefix ?>favicon.ico" rel="shortcut icon" type="image/ico" />
<link href="<?= $this->dirPrefix ?>assets/css/jstree/style.min.css" type="text/css" rel="stylesheet"/>
<link href="<?= $this->dirPrefix ?>assets/css/prism.css" type="text/css" rel="stylesheet" />
<link href="<?= $this->dirPrefix ?>assets/css/style.css" type="text/css" rel="stylesheet" />
<script src="<?= $this->dirPrefix ?>assets/js/jquery.min.js"></script>
<script src="<?= $this->dirPrefix ?>assets/js/jstree.min.js"></script>
<title>
    <?= strlen($this->pageTitle)!=0?($this->pageTitle . ' -- ' . $this->bookTitle):$this->bookTitle; ?>
</title>
</head>
<header>
    <div class="header">
        <div class="header-body">
            <div class="book-title">
                <a href="<?= $this->dirPrefix ?>index.html"><?php echo $this->bookTitle; ?></a>
            </div>
            <div class="book-subtitle">
                <?php echo $this->bookSubtitle; ?>
            </div>
            <nav>
                <div class="menu">
                    <?php foreach ($this->links as $linkText=>$linkUrl): ?>
                    <div class="link">
                        <a href="<?= $linkUrl ?>"><?= $linkText ?></a>
                    </div>
                    <?php endforeach; ?>

                </div>
            </nav>
        </div>    
    </div>
</header>    
<body>
<div id="container">
<?php echo $this->content; ?>        
</div>
<footer>
    <div class="footer">
        <div class="footer-body">
            <div class="copyright">
                Copyright <?= $this->copyright ?>
            </div>
            <div class="generated-by">
                Generated using <a href="https://github.com/olegkrivtsov/openbook">OpenBook</a> on <?php echo date('Y-m-d') ?> at <?php echo date('H:i') ?>
            </div>
        </div>    
    </div>
</footer>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-80824388-1', 'auto');
  ga('send', 'pageview');

</script>
</body>
</html>    

