<p>
    <?= _('In diesem Schritt legen Sie die Benutzerdaten für ein '
        . 'Haupt-Administrator-Konto (root) in Stud.IP fest.') ?>
    <?= _('Bitte merken Sie sich die Zugangsdaten!') ?>
</p>

<h3><?= _('Konto für Root einrichten') ?></h3>
<div class="type-text required">
    <label for="username" class="vertical"><?= _('Nutzername') ?></label>
    <input required type="text" id="username" name="username" minlength="4"
           value="<?= htmlReady(Request::get('username', $_SESSION['STUDIP_INSTALLATION']['root']['username'])) ?>">
</div>

<div class="type-text required">
    <label for="password" class="vertical"><?= _('Passwort') ?></label>
    <input required type="password" id="password" name="password" minlength="8">
</div>

<div class="type-text required">
    <label for="password_confirm" class="vertical"><?= _('Passwort bestätigen') ?></label>
    <input required type="password" id="password_confirm" name="password_confirm" minlength="8">
</div>

<div class="type-text required">
    <label for="first_name" class="vertical"><?= _('Vorname') ?></label>
    <input required type="text" id="first_name" name="first_name"
           value="<?= htmlReady(Request::get('first_name', $_SESSION['STUDIP_INSTALLATION']['root']['first_name'])) ?>">
</div>

<div class="type-text required">
    <label for="last_name" class="vertical"><?= _('Nachname') ?></label>
    <input required type="text" id="last_name" name="last_name"
           value="<?= htmlReady(Request::get('last_name', $_SESSION['STUDIP_INSTALLATION']['root']['last_name'])) ?>">
</div>

<div class="type-text required">
    <label for="email" class="vertical"><?= _('E-Mail-Adresse') ?></label>
    <input required type="email" id="email" name="email" value="<?= htmlReady(Request::get('user', $_SESSION['STUDIP_INSTALLATION']['root']['email'])) ?>">
</div>

<p style="margin-top: 1em;">
    <?= _('Wenn Sie jetzt auf installieren klicken,') ?>
    <ul>
        <li><?= _('werden die notwendigen Datenbanktabellen erzeugt') ?></li>
        <li><?= _('werden die ausgewählten Beispieldaten eingetragen') ?></li>
        <li><?= _('wird die Grundkonfiguration für das System in der Datenbank gespeichert') ?></li>
        <li><?= _('wird ein Root-Konto für Sie eingerichtet.') ?></li>
    </ul>
</p>
