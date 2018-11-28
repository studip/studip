<form method="post" class="default" action="<?= $controller->url_for('admin/holidays/edit/' . $holiday->id) ?>" data-dialog="size=auto">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Ferien bearbeiten') ?></legend>
        <label>
            <?= _('Name der Ferien') ?>
            <input required type="text" name="name" id="name" value="<?= htmlReady($holiday->name) ?>">
        </label>

        <label>
            <?= _('Beschreibung') ?>
            <textarea name="description" id="description"><?= htmlReady($holiday->description) ?></textarea>
        </label>

        <label class="col-3">
            <?= _('Ferienbeginn') ?>:
            <input required type="text" id="beginn" name="beginn"
                   data-date-picker='{"<=":"#ende"}'
                   value="<? if ($holiday->beginn) echo date('d.m.Y', $holiday->beginn) ?>">
           </label>

        <label class="col-3">
            <?= _('Ferienende') ?>:
            <input required type="text" id="ende" name="ende"
                   data-date-picker='{">=":"#beginn"}'
                   value="<? if ($holiday->ende) echo date('d.m.Y', $holiday->ende) ?>">
        </label>
   </fieldset>

    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/holidays')) ?>
    </div>

</form>
