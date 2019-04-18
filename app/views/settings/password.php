<? use Studip\Button; ?>

<form id="edit_password" method="post" action="<?= $controller->url_for('settings/password/store') ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <fieldset>
        <legend>
            <?= _('Passwort ändern') ?>
        </legend>
        <label>
            <span class="required"><?= _('Aktuelles Passwort') ?></span>
            <input required type="password" id="password" name="password">
        </label>
        <label>
            <span class="required"><?= _('Neues Passwort') ?></span>
            <input required type="password" pattern=".{8,}"
                   id="new_password" name="new_password"
                   data-message="<?= _('Das Passwort ist zu kurz. Es sollte mindestens 8 Zeichen lang sein.') ?>">
        </label>
        <label>
            <span class="required"><?= _('Passwort bestätigen') ?></span>
            <input required type="password" pattern=".{8,}"
                   id="new_password_confirm" name="new_password_confirm"
                   data-must-equal="#new_password">
        </label>
    </fieldset>
    <footer><?= Button::create(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?></footer>
</form>
