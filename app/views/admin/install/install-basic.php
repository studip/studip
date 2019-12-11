<h3><?= _('Installation') ?></h3>
<dl>
    <dt><?= _('Datenbank') ?></dt>
    <dd class="success">
        <?= _('Datenbanktabellen wurden erzeugt und ausgewählte Beispieldaten '
            . 'eingetragen') ?>
    </dd>

    <dt><?= _('Root-Konto') ?></dt>
    <dd class="success">
        <?= _('Das Konto für das Haupt-Administrator-Konto wurde eingerichtet') ?>
    </dd>

    <dt><?= _('Konfiguration') ?></dt>
<?php if ($local_inc === true): ?>
    <dd class="success">
        <?= sprintf(
            _('Konfiguration wurde in die Datenbank und die Dateien '
            . '%sconfig/config_local.inc.php%s sowie '
            . '%sconfig/config.inc.php%s geschrieben'),
            '<code>',
            '</code>',
            '<code>',
            '</code>'
        ) ?>
    </dd>
<?php else: ?>
    <dd class="failed">
        <?= _('Konnte nicht gespeichert werden. Bitte erzeugen Sie die beiden '
            . 'folgenden Dateien mit dem jeweiligen Inhalt:') ?>
        <br>

        <code>config/config_local.inc.php</code>:<br>
        <textarea onclick="this.select()"><?= htmlReady($local_inc) ?></textarea>

        <br>

        <code>config/config.inc.php</code>:<br>
        <textarea onclick="this.select()"><?= htmlReady($config_inc) ?></textarea>
    </dd>
<?php endif; ?>
</dl>
