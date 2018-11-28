<dl>
    <dt><?= _('Name') ?></dt>
    <dd><?= htmlReady($plugin['name']) ?></dd>

    <dt><?= _('Klasse') ?></dt>
    <dd><?= htmlReady($plugin['class']) ?></dd>

    <dt><?= _('Typ') ?></dt>
    <dd><?= join(', ', $plugin['type']) ?></dd>

    <dt><?= _('Origin') ?></dt>
    <dd><?= htmlReady($manifest['origin']) ?></dd>

    <dt><?= _('Version') ?></dt>
    <dd><?= htmlReady($manifest['version']) ?></dd>

    <dt><?= _('Beschreibung') ?></dt>
    <dd>
    <? if ($manifest['description']): ?>
        <?= htmlReady($manifest['description']) ?>
    <? else: ?>
        (<?= _('keine Beschreibung vorhanden') ?>)
    <? endif; ?>
    </dd>
</dl>
