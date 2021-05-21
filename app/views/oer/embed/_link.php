<div style="text-align: right;">
    <a href="<?= URLHelper::getLink("dispatch.php/oer/market/details/{$id}") ?>"
       title="<?= htmlReady(sprintf(_('Zum %s wechseln'), Config::get()->OER_TITLE)) ?>">
        <?= Icon::create('service')->asImg(['class' => 'text-bottom']) ?>
        <?= htmlReady($material['name']) ?>
    </a>
</div>
