<div style="text-align: right;">
    <a href="<?= URLHelper::getLink("dispatch.php/oer/market/details/".$id) ?>"
       title="<?= sprintf(_('Zum %'), Config::get()->OER_TITLE) ?>">
        <?= Icon::create("service", "clickable")->asImg(16, ['class' => "text-bottom"]) ?>
        <?= htmlReady($material['name']) ?>
    </a>
</div>
