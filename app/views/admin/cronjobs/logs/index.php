<? use Studip\Button, Studip\LinkButton; ?>

<form action="<?= $controller->filter() ?>" method="post" class="cronjob-filters default">
    <fieldset>
        <legend>
            <?= _('Darstellung einschränken') ?>

        <? if ($pagination->getTotal() != $total): ?>
            <small>
                <?= sprintf(_('Passend: %u von %u Logeinträgen'), $pagination->getTotal(), $total) ?>
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
            <?= LinkButton::createCancel(
                _('Zurücksetzen'),
                $controller->url_for('admin/cronjobs/logs/filter'),
                ['title' => _('Filter zurücksetzen')]
            ) ?>
        <? endif; ?>
    </footer>
</form>


<form action="<?= $controller->bulk($pagination->getCurrentPage()) ?>" method="post" class="default">
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
        <? if (count($logs) === 0): ?>
            <tr class="empty">
                <td colspan="6"><?= _('Keine Einträge vorhanden') ?></td>
            </tr>
        <? endif; ?>
        <? foreach ($logs as $log): ?>
            <tr id="log-<?= htmlReady($log->id) ?>">
                <td style="text-align: center">
                    <input type="checkbox" name="ids[]" value="<?= htmlReady($log->id) ?>">
                </td>
                <td><?= strftime('%x %X', $log->executed) ?></td>
                <td><?= strftime('%x %X', $log->scheduled) ?></td>
                <td><?= htmlReady($log->schedule->title ?: $log->schedule->task->name) ?></td>
                <td>
                <? if ($log->duration == -1): ?>
                    <?= Icon::create('question', Icon::ROLE_INACTIVE)->asImg(['title' => _('Läuft noch')]) ?>
                <? elseif ($log->exception === null): ?>
                    <?= Icon::create('accept', Icon::ROLE_STATUS_GREEN)->asImg(['title' => _('Ja')]) ?>
                <? else: ?>
                    <?= Icon::create('decline', Icon::ROLE_STATUS_RED)->asImg(['title' => _('Nein')]) ?>
                <? endif; ?>
                </td>
                <td style="text-align: right">
                    <a data-dialog href="<?= $controller->display($log, $pagination->getCurrentPage()) ?>">
                        <?= Icon::create('admin')->asImg(['title' => _('Logeintrag anzeigen')]) ?>
                    </a>
                    <a href="<?= $controller->delete($log, $pagination->getCurrentPage()) ?>">
                        <?= Icon::create('trash')->asImg(['title' => _('Logeintrag löschen')]) ?>
                    </a>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">
                    <select name="action" data-activates="button[name=bulk]">
                        <option value="">- <?= _('Aktion auswählen') ?></option>
                        <option value="delete"><?= _('Löschen') ?></option>
                    </select>
                    <?= Button::createAccept(_('Ausführen'), 'bulk') ?>

                    <section style="float: right">
                        <?= $pagination->asLinks(function ($page) use ($controller) {
                            return $controller->index($page);
                        }) ?>
                    </section>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
