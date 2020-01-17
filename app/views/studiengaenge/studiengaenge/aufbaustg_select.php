<form data-dialog action="<?= $controller->url_for('studiengaenge/studiengaenge/aufbaustg_assign') ?>" class="default" id="mvv-aufbaustg-new" method="post">
    <label for="mvv-aufbaustg-select">
        <?= _('Aufbaustudiengang auswählen') ?>
    </label>
    <select multiple id="mvv-aufbaustg-select" name="aufbaustg_ids[]" class="nested-select">
        <option class="is-placeholder"><?= _('Aufbaustudiengang auswählen') ?></option>
        <? foreach ($aufbaustgs as $stg) : ?>
        <option value="<?= $stg->id ?>"<?= in_array($stg->id, $aufbaustg_assigmnents) ? ' disabled' : '' ?>><?= htmlReady($stg->getDisplayName()) ?></option>
        <? endforeach; ?>
    </select>
    <input type="hidden" name="grundstg_id" value="<?= $grundstg->id ?>">
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store_aufbaustg') ?>
        <?= Studip\LinkButton::createCancel(); ?>
    </div>
</form>
