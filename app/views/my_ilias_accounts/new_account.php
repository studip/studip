<form class="default" action="<?= $controller->url_for('my_ilias_accounts/index/') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="ilias_new_account_index" value="<?=$ilias_index?>">
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