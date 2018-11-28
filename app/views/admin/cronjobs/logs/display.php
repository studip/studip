<dl class="cronlog">
    <dt><?= _('Cronjob') ?></dt>
    <dd><?= htmlReady($log->schedule->title) ?></dd>

    <dt><?= _('Aufgabe') ?></dt>
    <dd><?= htmlReady($log->schedule->task->name) ?></dd>

    <dt><?= _('Geplante Ausführung') ?></dt>
    <dd><?= date('d.m.Y H:i:s', $log->scheduled) ?></dd>

    <dt><?= _('Tatsächliche Ausführung') ?></dt>
    <dd><?= date('d.m.Y H:i:s', $log->executed) ?></dd>

    <dt><?= _('Ausführungsdauer') ?></dt>
    <dd>
    <? if ($log->duration == -1): ?>
        <?= _('Cronjob läuft noch oder wurde durch einen Fehler abgebrochen') ?>
    <? else: ?>
        <?= number_format($log->duration, 6, ',', '.') ?> <?= _('Sekunden') ?>
    <? endif; ?>
    </dd>

<? if (!empty($log->output)): ?>
    <dt><?= _('Ausgabe') ?></dt>
    <dd><pre><?= htmlReady($log->output) ?></pre></dd>
<? endif; ?>

<? if ($log->exception !== null): ?>
    <dt><?= _('Fehler') ?></dt>
    <dd>
        <?= nl2br(htmlReady($log->exception)) ?>
    </dd>
<? endif; ?>
</dl>
