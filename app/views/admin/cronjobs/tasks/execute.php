<h2><?= sprintf(_('Cronjob-Aufgabe "%s" ausführen'), htmlReady($task->name)) ?></h2>
<? if (isset($result)): ?>
<pre><code><?= htmlReady($result ?: _('- Keine Ausgabe -')) ?></code></pre>
<? else: ?>
<p><?= htmlReady($task->description) ?></p>
<form class="default" action="<?= $controller->execute($task) ?>" method="post" data-dialog="size=auto">
<? if (count($task->parameters)): ?>
    <?= $this->render_partial('admin/cronjobs/schedules/parameters') ?>
<? endif; ?>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Ausführen'), 'submit') ?>
    </div>
</form>
<? endif; ?>
