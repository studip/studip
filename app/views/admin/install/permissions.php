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
