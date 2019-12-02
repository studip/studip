<h3>Datenbank-Version</h3>
<dl>
    <dt><?= htmlReady($result['version']['present']) ?></dt>
<?php if ($result['version']['valid']): ?>
    <dd class="success">Ok</dd>
<?php else: ?>
    <dd class="failed">Fehler, mindestens MySQL/MariaDB <?= htmlReady($result['version']['required']) ?> benötigt</dd>
<?php endif; ?>
</dl>

<h3>Einstellungen</h3>
<dl>
<?php foreach ($result['settings'] as $setting => $state): ?>
    <dt><?= htmlReady($setting) ?></dt>
  <?php if ($state['valid']): ?>
    <dd class="success">Ok (<?= htmlReady($state['present']) ?> <?= htmlReady($state['cmp']) ?> <?= htmlReady($state['required']) ?>)</dd>
  <?php else: ?>
    <dd class="failed">Fehler, Wert ist <?= htmlReady($state['present']) ?> und muss <?= htmlReady($state['required']) ?> sein</dd>
  <?php endif; ?>
<?php endforeach; ?>
</dl>
