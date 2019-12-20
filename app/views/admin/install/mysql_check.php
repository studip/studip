<p>
    <?= _('In diesem Schritt wird geprüft, ob die Konfiguration der Datenbank '
        . 'korrekt ist.') ?>
    <?= _('Wenn alle Häkchen grün sind, können Sie fortfahren!') ?>
</p>

<h3><?= _('Datenbank-Version') ?></h3>
<dl>
    <dt><?= htmlReady($result['version']['present']) ?></dt>
<?php if ($result['version']['valid']): ?>
    <dd class="success"><?= _('Ok') ?></dd>
<?php else: ?>
    <dd class="failed">
        <?= sprintf(
            _('Fehler, mindestens MySQL/MariaDB %s benötigt'),
            htmlReady($result['version']['required'])
        ) ?>
    </dd>
<?php endif; ?>
</dl>

<?php if (!$result['version']['valid']): ?>
<p>
    <?= sprintf(
        _('Stud.IP benötigt eine MySQL/MariaDB-Datenbank ab mindestens Version %s.'),
        htmlReady($result['version']['required'])
    ) ?>
</p>
<?php endif; ?>

<h3><?= _('Einstellungen') ?></h3>
<dl>
<?php foreach ($result['settings']['settings'] as $setting => $state): ?>
    <dt><?= htmlReady($setting) ?></dt>
  <?php if ($state['valid']): ?>
    <dd class="success">
        <?= _('Ok') ?>
        (<?= htmlReady($state['present']) ?> <?= htmlReady($state['cmp']) ?> <?= htmlReady($state['required']) ?>)
    </dd>
  <?php else: ?>
    <dd class="failed">
        <?php if ($state['cmp'] === '!~=') : ?>
        <?= sprintf(
            _('Fehler, Wert ist %s und darf nicht %s sein'),
            htmlReady($state['present']),
            htmlReady($state['required'])
        ) ?>
        <?php else : ?>
        <?= sprintf(
            _('Fehler, Wert ist %s und muss %s sein'),
            htmlReady($state['present']) ?: _('(leer)'),
            htmlReady($state['required'])
        ) ?>
        <?php endif ?>
    </dd>
  <?php endif; ?>
<?php endforeach; ?>
</dl>

<?php if (!$result['settings']['valid']): ?>
<p>
    <?= _('Bitte ändern Sie die Einstellungen in Ihrer Datenbankkonfiguration '
        . 'oder wenden Sie sich an Ihren Hoster.') ?>
    <?= _('Klicken Sie anschließend auf „Erneut prüfen“.') ?>
</p>
<?php endif; ?>
