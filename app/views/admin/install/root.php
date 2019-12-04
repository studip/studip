<h3>Konto für Root einrichten</h3>
<div class="type-text">
    <label for="username">Nutzername</label>
    <input required type="text" id="username" name="username"
           value="<?= htmlReady(Request::get('username', $_SESSION['STUDIP_INSTALLATION']['root']['username'])) ?>">
</div>

<div class="type-text">
    <label for="password">Passwort</label>
    <input required type="password" id="password" name="password">
</div>

<div class="type-text">
    <label for="password_confirm">Passwort bestätigen</label>
    <input required type="password" id="password_confirm" name="password_confirm">
</div>

<div class="type-text">
    <label for="first_name">Vorname</label>
    <input required type="text" id="first_name" name="first_name"
           value="<?= htmlReady(Request::get('first_name', $_SESSION['STUDIP_INSTALLATION']['root']['first_name'])) ?>">
</div>

<div class="type-text">
    <label for="last_name">Nachname</label>
    <input required type="text" id="last_name" name="last_name"
           value="<?= htmlReady(Request::get('last_name', $_SESSION['STUDIP_INSTALLATION']['root']['last_name'])) ?>">
</div>

<div class="type-text">
    <label for="email">E-Mail-Adresse</label>
    <input required type="email" id="email" name="email" value="<?= htmlReady(Request::get('user', $_SESSION['STUDIP_INSTALLATION']['root']['email'])) ?>">
</div>
