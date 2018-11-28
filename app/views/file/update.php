<form action="<?= $controller->link_for('file/update/' . $file_ref->id, ['from_plugin' => $from_plugin]) ?>"
      enctype="multipart/form-data" method="post" class="default">

    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Neue Dateiversion') ?></legend>
        <label class="file-upload">
            <?= sprintf(
                _('Bitte die neue Version der Datei %s auswählen.'),
                htmlReady($file_ref->name)
            ) ?>
            <input type="file" name="file">
        </label>
        <label>
            <input type="checkbox" name="update_filename" value="1">
            <?= _('Dateinamen aus neuer Dateiversion übernehmen.') ?>
        </label>
    <? $count = count($file_ref->file->refs) ?>
    <? if ($count > 1) : ?>
        <label>
            <input type="checkbox" name="update_all_instances" value="1">
            <?= sprintf(_('Alle weiteren %u Vorkommen aktualisieren.'), $count - 1) ?>
        </label>
    <? endif ?>
    </fieldset>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Aktualisieren'), 'confirm') ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for(
                (in_array($folder->range_type, ['course', 'institute']) ? $folder->range_type . '/' : '')
                . 'files/index/' . $folder->parent_id
            )
        ) ?>
    </footer>
</form>
