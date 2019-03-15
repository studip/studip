<form class="default" action="<?= $controller->url_for('admin/ilias_interface/save/'.$ilias_index) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <label>
        <span class="required"><?= _('Rollen-Template zum Erstellen von Lernobjekten') ?></span>
        <input type="text" name="ilias_author_role_name" size="50" maxlength="255" value="<?= $ilias_config['author_role_name'] ? htmlReady($ilias_config['author_role_name']) : 'Author' ?>" required>
    </label>
    <label>
        <span class="required"><?= _('Erforderliche Rechtestufe zum Erstellen von Lernobjekten') ?></span>
        <select name="ilias_author_perm">
        	<option value="autor" <?=$ilias_config['author_perm'] == 'autor' ? 'selected' : ''?>><?=_('autor')?></option>
        	<option value="tutor" <?=$ilias_config['author_perm'] == 'tutor' ? 'selected' : ''?>><?=_('tutor')?></option>
        	<option value="dozent" <?=(($ilias_config['author_perm'] == 'dozent') OR ! $ilias_config['author_perm']) ? 'selected' : ''?>><?=_('dozent')?></option>
        	<option value="admin" <?=$ilias_config['author_perm'] == 'admin' ? 'selected' : ''?>><?=_('admin')?></option>
        	<option value="root" <?=$ilias_config['author_perm'] == 'root' ? 'selected' : ''?>><?=_('root')?></option>
        </select>
    </label>
    <label>
        <input type="checkbox" name="ilias_allow_change_account" value="1" <?= $ilias_config['allow_change_account'] ? 'checked' : '' ?>>
        <span><?= _('Stud.IP-User können sich bestehende ILIAS-Accounts manuell zuordnen') ?></span>
    </label>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'submit') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>