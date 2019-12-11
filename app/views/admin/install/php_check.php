<p>
    In diesem Schritt wird geprüft, ob die PHP-Konfiguration auf dem Server den
    Anforderungen von Stud.IP entspricht. Wenn alle Häkchen grün sind, können
    Sie fortfahren!
</p>

<h3>PHP-Version</h3>
<dl>
    <dt><?= htmlReady($result['version']['present']) ?></dt>
<?php if ($result['version']['valid']): ?>
    <dd class="success">Ok</dd>
<?php else: ?>
    <dd class="failed">Fehler, mindestens PHP <?= htmlReady($result['version']['required']) ?> benötigt</dd>
<?php endif; ?>
</dl>

<?php if (!$result['version']['valid']): ?>
<p>
    Bevor Sie mit der Installation fortfahren können, müssen Sie die PHP-Version
    Ihres Servers auf mind. Version <?= $result['version']['required'] ?>
    aktualisieren. Bei Problemen mit dem Aktualisieren Ihrer PHP-Version wenden
    Sie sich an Ihren Hoster.
</p>
<?php endif; ?>

<h3>PHP-Module</h3>
<dl>
<?php foreach ($result['modules']['required'] as $module => $requirement): ?>
    <dt><?= htmlReady($module) ?></dt>
  <?php if (!$result['modules']['present'][$module] && $requirement === true): ?>
    <dd class="failed">Nicht installiert</dd>
  <?php elseif (!$result['modules']['present'][$module] && $requirement): ?>
    <dd>Optional benötigt für "<?= htmlReady($requirement) ?>"</dd>
  <?php else: ?>
    <dd class="success">Ok</dd>
  <?php endif; ?>
<?php endforeach; ?>
</dl>

<?php if (!$result['modules']['valid']): ?>
<p>
    Mindestens ein PHP-Modul muss noch installiert werden, bevor Sie mit der
    Installation von Stud.IP fortfahren können. Bei Problemen mit der
    Installation von Modulen wenden Sie sich an Ihren Hoster.
</p>
<?php endif; ?>

<h3>PHP-Einstellungen</h3>
<dl>
<?php foreach ($result['settings']['settings'] as $setting => $state): ?>
    <dt><?= htmlReady($setting) ?></dt>
  <?php if ($state['valid']): ?>
    <dd class="success">Ok (<?= htmlReady($state['present']) ?> <?= htmlReady($state['cmp']) ?> <?= htmlReady($state['required']) ?>)</dd>
  <?php else: ?>
    <dd class="failed">Fehler, Wert ist <?= htmlReady($state['present'] ?: '(leer)') ?> und muss <?= htmlReady($state['required']) ?> sein</dd>
  <?php endif; ?>
<?php endforeach; ?>
</dl>

<?php if (!$result['settings']['valid']): ?>
<p>
    Die rot markierten Einstellungen müssen in der Datei
    <code><?= php_ini_loaded_file() ?></code>
    auf den angegebenen Wert gesetzt werden. Denken Sie daran, dass nach einer
    Änderung der Server neu gestartet werden muss! Wenn Sie dazu keine
    Berechtigung haben, wenden Sie sich an Ihren Hoster.
</p>
<?php endif; ?>
