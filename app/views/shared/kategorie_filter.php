<strong><?= _('Kategorie') ?></strong><br>
<form id="kategorie_filter" action="<?= $action ?>" method="post">
    <select name="kategorie_filter" size="1" style="width: 180px;" class="submit-upon-select">
        <option value=""><?= _('-- Kategorie wählen --') ?></option>
        <? foreach ($kategorien as $kategorie) : ?>
        <option value="<?= $kategorie->getId() ?>"<?= ($kategorie->getId() == $selected_kategorie ? ' selected' : '') ?>><?= htmlReady($kategorie->name) ?></option>
        <? endforeach; ?>
    </select>
</form>
