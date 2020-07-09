<div id="file_edit_window">
    <?= $this->render_partial('file/_file_aside.php', compact('file_ref')) ?>

    <div id="file_management_forms">
        <form method="post" data-dialog class="default"
            action="<?= $controller->link_for('/edit/' . $file_ref->id, ['from_plugin' => $from_plugin]) ?>">

            <?= CSRFProtection::tokenTag() ?>
            <fieldset>
                <legend><?= _('Datei bearbeiten') ?></legend>

                <label>
                    <?= _('Name') ?>
                    <input id="edit_file_name" type="text" name="name"
                           value="<?= htmlReady($name) ?>">
                </label>
                <label>
                    <?= _('Beschreibung') ?>
                    <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"><?= htmlReady($description); ?></textarea>
                </label>
            </fieldset>

            <?= $this->render_partial('file/_terms_of_use_select.php', [
                'content_terms_of_use_entries' => $content_terms_of_use_entries,
                'selected_terms_of_use_id'     => $content_terms_of_use_id,
            ]) ?>

            <footer data-dialog-button>
                <? if ($show_force_button): ?>
                    <?= Studip\Button::createAccept(_('Trotzdem speichern'), 'force_save') ?>
                <? else: ?>
                    <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
                <? endif ?>
                <?= Studip\LinkButton::createCancel(
                    _('Abbrechen'),
                    $controller->url_for((in_array($folder->range_type, ['course', 'institute']) ? $folder->range_type . '/' : '') . 'files/index/' . $folder->id)
                ) ?>
            </footer>
        </form>
    </div>
</div>

<script>
    //On focus on the edit file name input field, the whole file name but the
    //extension shall be selected. This can't be done with jQuery directly!

    jQuery('#edit_file_name').focus(function() {
        //select the whole file name, but the file extension
        var text = $(this).val(),
            //get start position of extension:
            extension_start_pos = text.lastIndexOf('.');

        $(this).setSelection(0, extension_start_pos);
    });

</script>
