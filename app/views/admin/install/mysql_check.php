<p>
    In diesem Schritt wird geprüft, ob die Konfiguration der Datenbank korrekt
    ist. Wenn alle Häkchen grün sind, können Sie fortfahren!
</p>

<h3>Datenbank-Version</h3>
<dl>
    <dt><?= htmlReady($result['version']['present']) ?></dt>
<?php if ($result['version']['valid']): ?>
    <dd class="success">Ok</dd>
<?php else: ?>
    <dd class="failed">Fehler, mindestens MySQL/MariaDB <?= htmlReady($result['version']['required']) ?> benötigt</dd>
<?php endif; ?>
</dl>

<?php if (!$result['version']['valid']): ?>
<p>
    Stud.IP benötigt eine MySQL/MariaDB-Datenbank ab mindestens Version
    <?= $result['version']['required'] ?>.
</p>
<?php endif; ?>

<h3>Einstellungen</h3>
<dl>
<?php foreach ($result['settings']['settings'] as $setting => $state): ?>
    <dt><?= htmlReady($setting) ?></dt>
  <?php if ($state['valid']): ?>
    <dd class="success">Ok (<?= htmlReady($state['present']) ?> <?= htmlReady($state['cmp']) ?> <?= htmlReady($state['required']) ?>)</dd>
  <?php else: ?>
    <dd class="failed">Fehler, Wert ist <?= htmlReady($state['present']) ?> und muss <?= htmlReady($state['required']) ?> sein</dd>
  <?php endif; ?>
<?php endforeach; ?>
</dl>

<?php if (!$result['settings']['valid']): ?>
<p>
    Bitte ändern Sie die Einstellungen in Ihrer Datenbankkonfiguration oder
    wenden Sie sich an Ihren Hoster.
    Klicken Sie anschließend auf „Erneut prüfen“.
</p>
<?php endif; ?>
