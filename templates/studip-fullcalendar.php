<section
    <? if (count($attributes)) : ?>
        <?= arrayToHtmlAttributes($attributes) ?>
    <? endif ?>
    data-title="<?= htmlReady($title)?>"
    data-config="<?= htmlReady(json_encode($config)) ?>">
</section>
