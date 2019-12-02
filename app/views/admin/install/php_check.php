<h3>PHP-Version</h3>
<dl>
    <dt><?= htmlReady($result['version']['present']) ?></dt>
<?php if ($result['version']['valid']): ?>
    <dd class="success">Ok</dd>
<?php else: ?>
    <dd class="failed">Fehler, mindestens PHP <?= htmlReady($result['version']['required']) ?> benötigt</dd>
<?php endif; ?>
</dl>

<h3>PHP-Module</h3>
<dl>
<?php foreach ($result['modules']['required'] as $module => $requirement): ?>
    <dt><?= htmlReady($module) ?></dt>
  <?php if (!$result['modules']['present'][$module] && $requirement === true): ?>
    <dd class="failed">Fehler</dd>
  <?php elseif (!$result['modules']['present'][$module] && $requirement): ?>
    <dd>Optional benötigt für "<?= htmlReady($requirement) ?>"</dd>
  <?php else: ?>
    <dd class="success">Ok</dd>
  <?php endif; ?>
<?php endforeach; ?>
</dl>

<h3>PHP-Einstellungen</h3>
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
