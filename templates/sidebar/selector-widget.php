<form action="<?= URLHelper::getLink($url, [], true) ?>" method="<?= $method?>" class="selector-widget">
    <?= ($method == 'post' ? CSRFProtection::tokenTag() : '') ?>
    <select name="<?=htmlReady($name)?>" class="sidebar-selectlist submit-upon-select text-top" size="<?= (int) $size ?: 8 ?>" aria-label="<?= _("Wählen Sie ein Objekt aus. Sie gelangen dann zur neuen Seite.") ?>">
    <? foreach ($elements as $element): ?>
        <option <? if ($element->isActive()) echo 'selected'; ?>
                value="<?= htmlReady($element->getid()) ?>"
                title="<?= htmlReady($element->getTooltip() !== null ? $element->getTooltip() : $element->getLabel()) ?>">
            <?= htmlReady(my_substr($element->getLabel(), 0, 30)) ?>
        </option>
    <? endforeach; ?>
    </select>
</form>
