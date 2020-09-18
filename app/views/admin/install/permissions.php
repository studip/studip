<p>
    <?= _('In diesem Schritt wird geprüft, ob ausgewählte Datenverzeichnisse '
        . 'beschreibbar sind.') ?>
    <?= _('Sie können fortfahren, wenn sichergestellt ist, dass die '
        . 'notwendigen Verzeichnisse schreibbar sind!') ?>
</p>
<p>
    <?= _('Sollte der Ordner "config" nicht schreibbar sein, wird die '
        . 'erzeugte Konfiguration am Ende der Installation angezeigt und '
        . 'muss von Hand in die entsprechenden Dateien kopiert werden.') ?>
</p>

<h3><?= _('Schreibbare Dateien/Ordner') ?></h3>
<dl>
<?php foreach ($writable['paths'] as $f => $is_writable): ?>
    <dt><?= htmlReady($f) ?></dt>
  <?php if ($is_writable): ?>
    <dd class="success"><?= _('Ok') ?></dd>
  <?php elseif ($requirements[$f]): ?>
    <dd class="failed"><?= _('Fehler, nicht schreibbar') ?></dd>
  <?php else: ?>
    <dd class="notice"><?= _('Nicht schreibbar (unproblematisch)') ?></dd>
  <?php endif; ?>
<?php endforeach; ?>
</dl>

<?php if (!$valid): ?>
<p>
    <?= _('Mindestens ein Verzeichnis kann nicht von der Anwendung beschrieben '
        . 'werden.') ?>
    <?= _('Ändern Sie die Berechtigungen für das Verzeichnis und klicken Sie '
        . 'auf „Erneut prüfen“.') ?>
</p>
<?php endif; ?>
