<dl class="requests">
    <dt data-request-url="<?= $controller->link_for('admin/install/install/sql') ?>" data-event-source="1">Datenbank</dt>
    <dd class="success">Installiert</dd>
    <dd class="failed">
        Fehler beim Installieren
        <div class="response"></div>
    </dd>
    <progress class="event-sourced" max="1" value="0"></progress>

    <dt data-request-url="<?= $controller->link_for('admin/install/install/root') ?>">Root-Konto</dt>
    <dd class="success">Eingerichtet</dd>
    <dd class="failed">
        Fehler beim Einrichten
        <div class="response"></div>
    </dd>

    <dt data-request-url="<?= $controller->link_for('admin/install/install/config') ?>">Konfiguration</dt>
    <dd class="success">Gespeichert</dd>
    <dd class="failed">
        Konnte nicht gespeichert werden. Bitte erzeugen Sie die beiden folgenden
        Dateien mit dem jeweiligen Inhalt:<br>

        <code>config/config_local.inc.php</code>:<br>
        <textarea onclick="this.select()" class="response" data-key="local_inc"></textarea>

        <br>

        <code>config/config.inc.php</code>:<br>
        <textarea onclick="this.select()" class="response" data-key="config_inc"></textarea>
    </dd>
</dl>
