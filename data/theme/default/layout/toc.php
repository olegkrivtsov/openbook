<div class="language-selection-small">
This book is available in: 
<?php foreach ($this->languages as $langCode=>$langName): ?>
<span class="lang-name <?= $this->currentLanguage==$langCode?"lang-name-active":"" ?>">
<a href="../<?= $langCode ?>/toc.html">
    <?= $langName ?>
</a>
</span>
<?php endforeach; ?>
</div>

<h1>Table of Contents</h1>

<div id="toc" style="display:none">
<?php echo $this->toc; ?>        
</div>

<div id="toc-ads">
<?= $this->tocAdContent ?>
</div>

<div class="clear"></div>

<?php
$this->externalStylesheets[] = 'assets/css/jstree/style.min.css';
$this->externalScripts[] = 'assets/js/jstree.min.js';
$this->inlineScripts[] = <<<EOT
<script type="text/javascript">
$(document).ready(function(){
    $('#toc').jstree({
        "core" : {
          "themes" : {
            "variant" : "large"
          },
          "force_text": true
        },
        "plugins" : [ "wholerow" ]
      }).on('ready.jstree', function (e, data) {
        $('#toc').show();
      });
      
    $('#toc').bind("select_node.jstree", function (e, data) {
        //var href = data.obj.children("a").attr("href");
        var href = data.node.a_attr.href;
        document.location.href = href;
      }); 
});
</script>
EOT;
?>