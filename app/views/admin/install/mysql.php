<p>
    In diesem Schritt verbinden Sie Ihre Stud.IP-Installation mit Ihrer
    bestehenden Datenbank.
</p>

<h3>Verbindungsdaten für die Datenbank</h3>
<div class="type-text required">
    <label for="host">Datenbank-Host</label>
    <input required type="text" id="host" name="host" value="<?= htmlReady(Request::get('host', $_SESSION['STUDIP_INSTALLATION']['database']['host'])) ?>">
</div>

<div class="type-text required">
    <label for="user">Datenbank-Nutzer</label>
    <input required type="text" id="user" name="user" value="<?= htmlReady(Request::get('user', $_SESSION['STUDIP_INSTALLATION']['database']['user'])) ?>">
</div>

<div class="type-text required">
    <label for="password">Datenbank-Passwort</label>
    <input required type="password" id="password" name="password" value="<?= htmlReady(Request::get('password', $_SESSION['STUDIP_INSTALLATION']['database']['password'])) ?>">
</div>

<div class="type-text required">
    <label for="database">Name der Datenbank</label>
    <input required type="text" id="database" name="database" value="<?= htmlReady(Request::get('database', $_SESSION['STUDIP_INSTALLATION']['database']['database'])) ?>">
</div>

<?php $button_label = $valid ? '' : 'Verbindung prüfen'; ?>
