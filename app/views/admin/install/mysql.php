<p>
    <?= _('In diesem Schritt verbinden Sie Ihre Stud.IP-Installation mit Ihrer '
        . 'bestehenden Datenbank.') ?>
</p>

<h3><?= _('Verbindungsdaten für die Datenbank') ?></h3>
<div class="type-text required">
    <label for="host"><?= _('Datenbank-Host') ?></label>
    <input required type="text" id="host" name="host" value="<?= htmlReady(Request::get('host', $_SESSION['STUDIP_INSTALLATION']['database']['host'])) ?>">
</div>

<div class="type-text required">
    <label for="user"><?= _('Datenbank-Nutzer') ?></label>
    <input type="text" id="user" name="user" value="<?= htmlReady(Request::get('user', $_SESSION['STUDIP_INSTALLATION']['database']['user'])) ?>">
</div>

<div class="type-text">
    <label for="password"><?= _('Datenbank-Passwort') ?></label>
    <input type="password" id="password" name="password" value="<?= htmlReady(Request::get('password', $_SESSION['STUDIP_INSTALLATION']['database']['password'])) ?>">
</div>

<div class="type-text required">
    <label for="database"><?= _('Name der Datenbank') ?></label>
    <input required type="text" id="database" name="database" value="<?= htmlReady(Request::get('database', $_SESSION['STUDIP_INSTALLATION']['database']['database'])) ?>">
</div>

<div class="type-checkbox">
    <input type="checkbox" name="create" id="create" value="1" checked
           class="studip-checkbox">
    <label for="create">
        <?= _('Versuche Datenbank anzulegen, falls sie noch nicht existiert') ?>
    </label>
</div>

<?php $button_label = $valid ? '' : _('Verbindung prüfen'); ?>
