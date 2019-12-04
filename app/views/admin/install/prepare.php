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

<h3>Daten zum System</h3>
<div class="type-text">
    <label for="system-name" class="vertical">
        Name der Stud.IP-Installation
    </label>
    <input required type="text" id="system-name" name="system_name"
           value="<?= htmlReady(Request::get('system_name', $_SESSION['STUDIP_INSTALLATION']['system']['name'])) ?>">
</div>

<div class="type-text">
    <label for="system-id" class="vertical">
        Id der Stud.IP-Installation
    </label>
    <input required type="text" id="system-id" name="system_id"
           value="<?= htmlReady(Request::get('system_id', $_SESSION['STUDIP_INSTALLATION']['system']['id'])) ?>">
</div>

<div class="type-text">
    <label for="system-url" class="vertical">
        URL der Stud.IP-Installation
    </label>
    <input required type="url" id="system-url" name="system_url"
           value="<?= htmlReady(Request::get('system_url', $_SESSION['STUDIP_INSTALLATION']['system']['url'])) ?>">
</div>

<div class="type-text">
    <label for="system-url" class="vertical">
        E-Mail-Adresse für Kontakt
    </label>
    <input required type="email" id="system-email" name="system_email"
           value="<?= htmlReady(Request::get('system_email', $_SESSION['STUDIP_INSTALLATION']['system']['email'])) ?>">
</div>

<div class="type-text">
    <label for="system-host-url" class="vertical">
        URL der betreibenden Einrichtung
    </label>
    <input type="url" id="system-host-url" name="system_host_url"
           value="<?= htmlReady(Request::get('system_host_url', $_SESSIOn['STUDIP_INSTALLATION']['system']['host_url'])) ?>">
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
