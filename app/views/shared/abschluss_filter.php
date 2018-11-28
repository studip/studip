<strong><?= _('Abschluss') ?></strong><br>
<form id="abschluss_filter" action="<?= $action ?>" method="post">
    <select name="abschluss_filter" size="1" style="width: 180px;" class="submit-upon-select">
        <option value=""><?= _('-- Abschluss wählen --') ?></option>
        <? foreach ($abschluesse as $abschluss) : ?>
        <option value="<?= $abschluss->getId() ?>"<?= ($abschluss->getId() == $selected_abschluss ? ' selected' : '') ?>><?= htmlReady($abschluss->name) ?></option>
        <? endforeach; ?>
    </select>
</form>
