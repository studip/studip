<div class="posting bg2" style="margin-bottom: 20px; display: none; position: relative; text-align: left;">
    <span class="title" style="padding-left: 5px; font-weight: bold">
        <?= _('Vorschau ihres Beitrags:') ?> (<?= _('Vergessen Sie nicht, ihren Beitrag zu speichern!')?>)
        <br><br>
    </span>
    
    <?= Icon::create('decline', 'attention', ['title' => _('Vorschaufenster schließen')])->asImg(16, ["style" => 'position: absolute; top: 5px; right: 5px; cursor: pointer;', "onClick" => 'jQuery(this).parent().hide();']) ?>

    <div class="postbody" id="<?= $preview_id ?>"></div>
    <br style="clear: both">
</div>