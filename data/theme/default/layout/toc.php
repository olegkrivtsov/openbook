
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

<div id="toc">
<?php echo $this->toc; ?>        
</div>

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
      });
      
    $('#toc').bind("select_node.jstree", function (e, data) {
        //var href = data.obj.children("a").attr("href");
        var href = data.node.a_attr.href;
        document.location.href = href;
      }); 
});    
</script>