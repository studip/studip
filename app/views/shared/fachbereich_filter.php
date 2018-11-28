<strong><?= _('Fachbereiche') ?></strong><br>
<form id="fachbereich_filter" action="<?= $action ?>" method="post">
    <select name="fachbereich_filter" size="1" style="width: 180px;" class="submit-upon-select">
        <option value=""><?= _('-- Fachbereich wählen --') ?></option>
        <? foreach ($fachbereiche as $fachbereich) : ?>
        <option value="<?= $fachbereich['fachbereich_id'] ?>"<?= ($fachbereich->getId() == $selected_fachbereich ? ' selected' : '') ?>><?= htmlReady($fachbereich->getDisplayName()) ?></option>
        <? endforeach; ?>
    </select>
</form>
