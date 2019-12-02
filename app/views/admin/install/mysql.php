<h3>Datenbankverbindung</h3>
<div class="type-text">
    <label for="host">Host</label>
    <input required type="text" id="host" name="host" value="<?= htmlReady(Request::get('post', $_SESSION['STUDIP_INSTALLATION']['database']['host'])) ?>">
</div>

<div class="type-text">
    <label for="user">Nutzer</label>
    <input required type="text" id="user" name="user" value="<?= htmlReady(Request::get('user', $_SESSION['STUDIP_INSTALLATION']['database']['user'])) ?>">
</div>

<div class="type-text">
    <label for="password">Passwort</label>
    <input required type="password" id="password" name="password" value="<?= htmlReady(Request::get('password', $_SESSION['STUDIP_INSTALLATION']['database']['password'])) ?>">
</div>

<div class="type-text">
    <label for="database">Datenbank</label>
    <input required type="text" id="database" name="database" value="<?= htmlReady(Request::get('database', $_SESSION['STUDIP_INSTALLATION']['database']['database'])) ?>">
</div>

<?php $button_label = $valid ? '' : 'Verbindung prüfen'; ?>
