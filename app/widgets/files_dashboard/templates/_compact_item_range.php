<? if ($rangeLink = $controller->getRangeLink($currentFolder)) : ?>
    <a href="<?= $rangeLink ?>">
        <?= htmlReady($widget->getRangeLabel($currentFolder) ?: _('Zum Speicherort')) ?>
    </a>
<? endif ?>
