<h3>Installation</h3>
<dl>
    <dt>Datenbank</dt>
    <dd class="success">
        Datenbanktabellen wurden erzeugt und ausgewählte Beispieldaten
        eingetragen
    </dd>

    <dt>Root-Konto</dt>
    <dd class="success">
        Das Konto für das Haupt-Administrator-Konto wurde eingerichtet
    </dd>

    <dt>Konfiguration</dt>
<?php if ($local_inc === true): ?>
    <dd class="success">
        Konfiguration wurde in die Datenbank und die Dateien
        <code>config/config_local.inc.php</code>
        sowie <code>config/config.inc.php</code> geschrieben
    </dd>
<?php else: ?>
    <dd class="failed">
        Konnte nicht gespeichert werden. Bitte erzeugen Sie die beiden folgenden
        Dateien mit dem jeweiligen Inhalt:<br>

        <code>config/config_local.inc.php</code>:<br>
        <textarea onclick="this.select()"><?= htmlReady($local_inc) ?></textarea>

        <br>

        <code>config/config.inc.php</code>:<br>
        <textarea onclick="this.select()"><?= htmlReady($config_inc) ?></textarea>
    </dd>
<?php endif; ?>
</dl>
