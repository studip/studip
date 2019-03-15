<form class="default" action="<?= $controller->url_for('my_ilias_accounts/change_account/'.$ilias_index.'/add') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <label>
        <span class="required"><?= _('Login') ?></span>
        <input type="text" name="ilias_login" size="50" maxlength="50" required>
    </label>
    <label>
        <span class="required"><?= _('Passwort') ?></span>
        <input type="password" name="ilias_password" size="50" maxlength="50" required>
    </label>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Zuordnen'), 'set_new_account') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>