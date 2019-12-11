<h3><?= _('Installation') ?></h3>
<dl class="requests">
    <dt data-request-url="<?= $controller->link_for('install/sql') ?>" data-event-source="1">
        <?= _('Datenbank') ?>
    </dt>
    <dd class="success">
        <?= _('Datenbanktabellen wurden erzeugt und ausgewählte Beispieldaten '
            . 'eingetragen') ?>
    </dd>
    <dd class="failed">
        <?= _('Fehler beim Installieren') ?>
        <div class="response"></div>
    </dd>
    <progress class="event-sourced" max="1" value="0"></progress>

    <dt data-request-url="<?= $controller->link_for('install/root') ?>">
        <?= _('Root-Konto') ?>
    </dt>
    <dd class="success">
        <?= _('Das Konto für das Haupt-Administrator-Konto wurde eingerichtet') ?>
    </dd>
    <dd class="failed">
        <?= _('Fehler beim Einrichten') ?>
        <div class="response"></div>
    </dd>

    <dt data-request-url="<?= $controller->link_for('install/config') ?>">
        <?= _('Konfiguration') ?>
    </dt>
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
    <dd class="failed">
        <?= _('Konnte nicht gespeichert werden. Bitte erzeugen Sie die beiden '
            . 'folgenden Dateien mit dem jeweiligen Inhalt:') ?>
        <br>

        <code>config/config_local.inc.php</code>:<br>
        <textarea onclick="this.select()" class="response" data-key="local_inc"></textarea>

        <br>

        <code>config/config.inc.php</code>:<br>
        <textarea onclick="this.select()" class="response" data-key="config_inc"></textarea>
    </dd>
</dl>
