<form class="default" action="<?= $controller->url_for('new_password/set/' . $token_id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Neues Passwort setzen') ?>
        </legend>

        <label>
            <?= _('Neues Passwort') ?>
            <input type="password" name="new_password" placeholder="<?= _('Neues Passwort') ?>">
        </label>

        <label>
            <?= _('Neues Passwort wiederholen') ?>
            <input type="password" name="new_password_confirm" placeholder="<?= _('Neues Passwort wiederholen') ?>">
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::createAccept(_('Passwort setzen')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('start')) ?>
    </footer>
</form>
