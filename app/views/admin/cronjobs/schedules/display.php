<dl class="cronjob">
    <dt><?= _('Titel') ?></dt>
    <dd><?= htmlReady($schedule->title) ?></dd>

<? if ($schedule->description): ?>
    <dt><?= _('Beschreibung') ?></dt>
    <dd><?= htmlReady($schedule->description) ?></dd>
<? endif; ?>

    <dt><?= _('Aktiv') ?></dt>
    <dd><?= $schedule->active ? _('Ja') : _('Nein') ?></dd>

    <dt><?= _('Priorität') ?></dt>
    <dd><?= CronjobSchedule::describePriority($schedule->priority) ?></dd>

<? if (count($schedule->parameters) > 0): ?>
    <dt><?= _('Parameter') ?></dt>
    <dd>
        <ul>
        <? foreach ($schedule->parameters as $parameter): ?>
            <li><?= htmlReady($parameter) ?></li>
        <? endforeach; ?>
        </ul>
    </dd>
<? endif; ?>

    <dt><?= _('Aufgabe') ?></dt>
    <dd><?= htmlReady($schedule->task->name) ?></dd>

    <dt><?= _('Typ') ?></dt>
<? if ($schedule->type === 'once'): ?>
    <dd>
        <?= sprintf(_('Einmalig am %s um %s'), date('d.m.Y', $schedule->next_execution), date('H:i', $schedule->next_execution)) ?>
    </dd>

    <dt><?= _('Ausgeführt') ?>?</dt>
    <dd>
    <? if ($schedule->execution_count > 0): ?>
        <?= _('Ja') ?>, <?= sprintf(_('am %s um %s'), date('d.m.Y', $schedule->last_execution), date('H:i:s', $schedule->last_execution)) ?>
    <? else: ?>
        <?= _('Nein') ?>
    <? endif; ?>
    </dd>
<? else: ?>
    <dd>
        <?= _('Regelmässig') ?>
        <?= $this->render_partial('admin/cronjobs/schedules/periodic-schedule', $schedule->toArray()) ?>
    </dd>

    <dt><?= _('Ausführungen') ?></dt>
    <dd><?= number_format($schedule->execution_count, 0, ',', '.') ?></dd>

    <? if ($schedule->active): ?>
        <dt><?= _('Nächste Ausführung') ?></dt>
        <dd><?= date('d.m.Y H:i:s', $schedule->next_execution) ?></dd>
    <? endif; ?>

    <? if ($schedule->execution_count > 0): ?>
        <dt><?= _('Letzte Ausführung') ?></dt>
        <dd><?= $schedule->last_execution ? date('d.m.Y H:i:s', $schedule->last_execution) : _('nie') ?></dd>

        <dt><?= _('Letztes Ergebnis') ?></dt>
        <dd><code><?= htmlReady($schedule->last_result) ?></code></dd>
    <? endif; ?>

<? endif; ?>
</dl>

<div data-dialog-button>>
    <a href="<?= $controller->link_for('admin/cronjobs/logs/schedule', $schedule) ?>">
        <?= _('Log anzeigen') ?>
    </a>
    <a href="<?= $controller->edit($schedule) ?>">
        <?= _('Cronjob bearbeiten') ?>
    </a>
</div>
