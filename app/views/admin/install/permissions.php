<p>
    In diesem Schritt wird geprüft, ob ausgewählte Datenverzeichnisse
    beschreibbar sind. Wenn alle Häkchen grün sind, können Sie fortfahren!
</p>

<h3>Schreibbare Dateien/Ordner</h3>
<dl>
<?php foreach ($writable as $f => $is_writable): ?>
    <dt><?= htmlReady($f) ?></dt>
  <?php if ($is_writable): ?>
    <dd class="success">Ok</dd>
  <?php else: ?>
    <dd class="failed">Fehler, nicht schreibbar</dd>
  <?php endif; ?>
<?php endforeach; ?>
</dl>

<?php if (!$valid): ?>
<p>
    Mindestens ein Verzeichnis kann nicht von der Anwendung beschrieben werden.
    Ändern Sie die Berechtigungen für das Verzeichnis und klicken Sie auf
    „Erneut prüfen“.
</p>
<?php endif; ?>
