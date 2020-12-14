<form class="default" action="<?= $controller->url_for('new_password/send_mail') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Mail zum Zurücksetzen des Passworts anfordern') ?>
        </legend>

        <label>
            <?= _('Geben sie die Mail-Adresse des Zugangs an, für den sie das Passwort zurücksetzen möchten') ?>
            <input type="text" name="mail" placeholder="<?= _('Ihre Mail-Adresse') ?>" required>
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::createAccept(_('Mail zuschicken')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('start')) ?>
    </footer>
</form>
