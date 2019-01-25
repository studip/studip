<form class="default" action="<?= $controller->url_for('admin/ilias_interface/save/'.$ilias_index) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <? if (count($existing_indices)) : ?>
    <label>
        <span class="required"><?= _('Art der Verknüpfung') ?></span>
        <select name="ilias_index">
        <option selected><?=_('Neue Verknüpfung')?></option>
        <? foreach ($existing_indices as $existing_index => $data) : ?>
            <option><?=sprintf(_('ILIAS-Installation (Index %s) aus vorheriger Verknüpfung'), $existing_index)?></option>
        <? endforeach ?>
        </select>
    </label>
    <? endif ?>
    <label>
        <span class="required"><?= _('Name der Installation') ?></span>
        <input type="text" name="ilias_name" size="50" maxlength="255" value="<?= htmlReady($ilias_config['name']) ?>" required>
    </label>
    <label>
        <span class="required">  <?= _('URL') ?></span>
        <input type="text" name="ilias_url" size="50" maxlength="255" value="<?= $ilias_config['url'] ?>" required>
    </label>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'submit') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>