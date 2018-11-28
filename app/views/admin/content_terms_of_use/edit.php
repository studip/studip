<form class="default" method="post" action="<?= $controller->link_for('admin/content_terms_of_use/store?id=' . $entry->id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>

    <? if ($entry->isNew()): ?>
        <label>
            <?= _('ID') ?>
            <input type="text" name="entry_id" value="<?= htmlReady($entry->id) ?>">
        </label>
    <? else: ?>
        <input type="hidden" name="entry_id" value="<?= htmlReady($entry->id) ?>">
    <? endif ?>
        <label>
            <?= _('Name') ?>
            <?= I18N::input('name', $entry->name)?>
        </label>
        <label>
            <?= _('Bedingung zum Herunterladen') ?>
            <select name="download_condition">
            <? foreach (ContentTermsOfUse::getConditions() as $condition_id => $description): ?>
                <option value="<?= htmlReady($condition_id) ?>" <? if ($entry->download_condition == $condition_id) echo 'selected'; ?>>
                    <?= htmlReady($description) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>
        <label>
            <?= _('Symbol-Name oder URL') ?>
            <input type="text" name="icon" value="<?= htmlReady($entry->icon) ?>">
        </label>
    </fieldset>

    <fieldset>
        <legend><?= _('Zusätzliche Angaben') ?></legend>

        <label>
            <?= _('Position') ?>
            <input type="number" name="position" value="<?= htmlReady($entry->position) ?>">
        </label>
        <label>
            <input type="checkbox" name="is_default" value="1"
                   <? if ($entry->is_default) echo 'checked'; ?>>
           <?= _('Standardlizenz bei neuen Dateien') ?>
        </label>
        <label>
            <?= _('Beschreibung') ?>
            <?= I18N::textarea('description', $entry->description)?>
        </label>
        <label>
            <?= _('Hinweise zur Nutzung') ?>
            <?= I18N::textarea('student_description', $entry->student_description)?>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for('admin/content_terms_of_use/index')
        ) ?>
    </footer>
</form>
