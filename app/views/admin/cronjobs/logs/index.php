<? use Studip\Button, Studip\LinkButton; ?>

<form action="<?= $controller->link_for('admin/cronjobs/logs/filter') ?>"
      method="post" class="cronjob-filters default">

    <fieldset>
        <legend>
            <?= _('Darstellung einschränken') ?>

        <? if ($total_filtered != $total): ?>
            <small>
                <?= sprintf(_('Passend: %u von %u Logeinträgen'), $total_filtered, $total) ?>
            </small>
        <? endif; ?>
        </legend>

        <label class="col-2">
            <?= _('Status') ?>
            <select name="filter[status]" id="status" class="submit-upon-select">
                <option value=""><?= _('Alle Logeinträge anzeigen') ?></option>
                <option value="passed" <? if ($filter['status'] === 'passed') echo 'selected'; ?>>
                    <?= _('Nur fehlerfreie Logeinträge anzeigen') ?>
                </option>
                <option value="failed" <? if ($filter['status'] === 'failed') echo 'selected'; ?>>
                    <?= _('Nur fehlerhafte Logeinträge anzeigen') ?>
                </option>
            </select>
        </label>

        <label class="col-2">
            <?= _('Cronjob') ?>
            <select name="filter[schedule_id]" id="schedule_id" class="submit-upon-select">
                <option value=""><?= _('Alle Logeinträge anzeigen') ?></option>
            <? foreach ($schedules as $schedule): ?>
                <option value="<?= $schedule->schedule_id ?>" <? if ($filter['schedule_id'] === $schedule->schedule_id) echo 'selected'; ?>>
                    <?= htmlReady($schedule->title) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label class="col-2">
            <?= _('Aufgabe') ?>
            <select name="filter[task_id]" id="task_id" class="submit-upon-select">
                <option value=""><?= _('Alle Aufgaben anzeigen') ?></option>
            <? foreach ($tasks as $task): ?>
                <option value="<?= $task->task_id ?>" <? if ($filter['task_id'] === $task->task_id) echo 'selected'; ?>>
                    <?= htmlReady($task->name) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>
    </fieldset>

    <footer>
        <noscript>
            <?= Button::create(_('Filtern')) ?>
        </noscript>

        <? if (!empty($filter)): ?>
            <?= LinkButton::createCancel(_('Zurücksetzen'),
                                         $controller->url_for('admin/cronjobs/logs/filter'),
                                         array('title' => _('Filter zurücksetzen'))) ?>
        <? endif; ?>
    </footer>
</form>


<form action="<?= $controller->url_for('admin/cronjobs/logs/bulk', $page) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

<table class="default cronjobs">
    <colgroup>
        <col width="20px">
        <col width="150px">
        <col width="150px">
        <col>
        <col width="50px">
        <col width="50px">
    </colgroup>
    <thead>
        <tr>
            <th>
                <input type="checkbox" name="all" value="1"
                       data-proxyfor=":checkbox[name='ids[]']"
                       data-activates=".cronjobs select[name=action]">
            </th>
            <th><?= _('Ausgeführt') ?></th>
            <th><?= _('Geplant') ?></th>
            <th><?= _('Cronjob') ?></th>
            <th><?= _('Ok?') ?></th>
            <th><?= _('Optionen') ?></th>
        </tr>
    </thead>
    <tbody>
<? for ($i = 0; $i < $max_per_page; $i += 1): ?>
    <? if (!isset($logs[$i])): ?>
        <tr class="empty">
            <td colspan="6">&nbsp;</td>
        </tr>
    <? else: ?>
        <tr id="log-<?= $logs[$i]->log_id ?>">
            <td style="text-align: center">
                <input type="checkbox" name="ids[]" value="<?= $logs[$i]->log_id ?>">
            </td>
            <td><?= strftime('%x %X', $logs[$i]->executed) ?></td>
            <td><?= strftime('%x %X', $logs[$i]->scheduled) ?></td>
            <td><?= htmlReady($logs[$i]->schedule->title ?: $logs[$i]->schedule->task->name) ?></td>
            <td>
            <? if ($logs[$i]->duration == -1): ?>
                <?= Icon::create('question', 'inactive', ['title' => _('Läuft noch')])->asImg() ?>
            <? elseif ($logs[$i]->exception === null): ?>
                <?= Icon::create('accept', 'status-green', ['title' => _('Ja')])->asImg() ?>
            <? else: ?>
                <?= Icon::create('decline', 'status-red', ['title' => _('Nein')])->asImg() ?>
            <? endif; ?>
            </td>
            <td style="text-align: right">
                <a data-dialog href="<?= $controller->link_for('admin/cronjobs/logs/display', $logs[$i]->log_id, $page) ?>">
                    <?= Icon::create('admin', 'clickable', ['title' => _('Logeintrag anzeigen')])->asImg() ?>
                </a>
                <a href="<?= $controller->link_for('admin/cronjobs/logs/delete', $logs[$i]->log_id, $page) ?>">
                    <?= Icon::create('trash', 'clickable', ['title' => _('Logeintrag löschen')])->asImg() ?>
                </a>
            </td>
        </tr>
    <? endif; ?>
<? endfor; ?>
    </tbody>
</table>
    <footer>
        <select name="action" data-activates="button[name=bulk]">
            <option value="">- <?= _('Aktion auswählen') ?></option>
            <option value="delete"><?= _('Löschen') ?></option>
        </select>
        <?= Button::createAccept(_('Ausführen'), 'bulk') ?>

        <section style="float: right">
            <?= Pagination::create($total_filtered, $page, $max_per_page)->asLinks(function ($page) use ($controller) {
                return $controller->index($page);
            }) ?>
        </section>
    </footer>
</form>
