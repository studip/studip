<p>
    <?= _('In diesem Schritt wird geprüft, ob die PHP-Konfiguration auf dem '
        . 'Server den Anforderungen von Stud.IP entspricht.') ?>
    <?= _('Wenn alle Häkchen grün sind, können Sie fortfahren!') ?>
</p>

<h3><?= _('PHP-Version') ?></h3>
<dl>
    <dt><?= htmlReady($result['version']['present']) ?></dt>
<?php if ($result['version']['valid']): ?>
    <dd class="success"><?= _('Ok') ?></dd>
<?php else: ?>
    <dd class="failed">
        <?= sprintf(
            _('Fehler, mindestens PHP %s benötigt'),
            htmlReady($result['version']['required'])
        ) ?>
    </dd>
<?php endif; ?>
</dl>

<?php if (!$result['version']['valid']): ?>
<p>
    <?= sprintf(
        _('Bevor Sie mit der Installation fortfahren können, müssen Sie die '
        . 'PHP-Version Ihres Servers auf mind. Version %s aktualisieren.'),
        htmlReady($result['version']['required'])
    ) ?>
    <?= _('Bei Problemen mit dem Aktualisieren Ihrer PHP-Version wenden Sie '
        . 'sich an Ihren Hoster.') ?>
</p>
<?php endif; ?>

<h3><?= _('PHP-Module') ?></h3>
<dl>
<?php foreach ($result['modules']['required'] as $module => $requirement): ?>
    <dt><?= htmlReady($module) ?></dt>
  <?php if (!$result['modules']['present'][$module] && $requirement === true): ?>
    <dd class="failed"><?= _('Nicht installiert') ?></dd>
  <?php elseif (!$result['modules']['present'][$module] && $requirement): ?>
    <dd>
        <?= sprintf(
            _('Optional benötigt für "%s"'),
            htmlReady($requirement)
        ) ?>
    </dd>
  <?php else: ?>
    <dd class="success"><?= _('Ok') ?></dd>
  <?php endif; ?>
<?php endforeach; ?>
</dl>

<?php if (!$result['modules']['valid']): ?>
<p>
    <?= _('Mindestens ein PHP-Modul muss noch installiert werden, bevor Sie '
        . 'mit der Installation von Stud.IP fortfahren können.') ?>
    <?= _('Bei Problemen mit der Installation von Modulen wenden Sie sich an '
        . 'Ihren Hoster.') ?>
</p>
<?php endif; ?>

<h3><?= _('PHP-Einstellungen') ?></h3>
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
        <?= sprintf(
            _('Fehler, Wert ist %s und muss %s sein'),
            htmlReady($state['present']) ?: _('(leer)'),
            htmlReady($state['required'])
        ) ?>
    </dd>
  <?php endif; ?>
<?php endforeach; ?>
</dl>

<?php if (!$result['settings']['valid']): ?>
<p>
    <?= sprintf(
        _('Die rot markierten Einstellungen müssen in der Datei %s%s%s '
        . 'auf den angegebenen Wert gesetzt werden.'),
        '<code>',
        htmlReady(php_ini_loaded_file()),
        '</code>'
    ) ?>

    <?= _('Denken Sie daran, dass nach einer Änderung der Server neu gestartet '
        . 'werden muss!') ?>
    <?= _('Wenn Sie dazu keine Berechtigung haben, wenden Sie sich an Ihren Hoster.') ?>
</p>
<?php endif; ?>
