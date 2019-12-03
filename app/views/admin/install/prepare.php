<h3>SQL-Daten einspielen</h3>
<ul class="list-unstyled">
<?php $i = 0; foreach ($files as $file => $description): ?>
    <li>
    <?php if (in_array($file, $required)): ?>
        <input type="hidden" name="files[]" value="<?= htmlReady($file) ?>">
        <input type="checkbox" checked disabled class="studip-checkbox">
    <?php else: ?>
        <input type="checkbox" name="files[]" value="<?= htmlReady($file) ?>"
               class="studip-checkbox" id="option-<?= $i ?>">
    <?php endif; ?>
        <label for="option-<?= $i ?>">
            <strong><?= htmlReady($description) ?></strong>
            (<?= htmlReady($file) ?>)
        </label>
    </li>
<?php $i += 1; endforeach; ?>
</ul>

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

<h3>Stud.IP-Umgebung</h3>
<div class="type-text">
    <label class="plain">
        <input type="radio" name="env" value="development"
               <?php if ($_SESSION['STUDIP_INSTALLATION']['env'] === 'development') echo 'checked'; ?>>
        Entwicklungsmodus
    </label>
</div>

<div class="type-text">
    <label class="plain">
        <input type="radio" name="env" value="production"
               <?php if ($_SESSION['STUDIP_INSTALLATION']['env'] === 'production') echo 'checked'; ?>>
        Produktivmodus
    </label>
</div>
