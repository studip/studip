<? use Studip\Button, Studip\LinkButton; ?>

<?php
    $days_of_week = [
        1 => _('Montag'),
        2 => _('Dienstag'),
        3 => _('Mittwoch'),
        4 => _('Donnerstag'),
        5 => _('Freitag'),
        6 => _('Samstag'),
        7 => _('Sonntag'),
    ];
?>

<form action="<?= $controller->edit($schedule, $page) ?>" method="post" class="cronjobs-edit default">
    <?= CSRFProtection::tokenTag() ?>

    <h1>
    <? if ($schedule->isNew()): ?>
        <?= _('Neuen Cronjob anlegen') ?>
    <? else: ?>
        <?= htmlReady(sprintf(_('Cronjob "%s" bearbeiten'), $schedule->title)) ?>
    <? endif; ?>
    </h1>

    <fieldset>
        <legend>
            <?= _('Details') ?>
        </legend>

        <label>
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" id="active" value="1"
                <? if ($schedule->active) echo 'checked'; ?>>
            <?= _('Aktiv') ?>
        </label>

        <label>
            <?= _('Priorität') ?>
            <select name="priority" id="priority">
            <? foreach (CronjobSchedule::getPriorities() as $priority => $label): ?>
                <option value="<?= $priority ?>" <? if ((!$schedule->priority && $priority === CronjobSchedule::PRIORITY_NORMAL) || $schedule->priority === $priority) echo 'selected'; ?>>
                    <?= htmlReady($label) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= _('Titel') ?>
            <input type="text" name="title" id="title" value="<?= htmlReady($schedule->title ?: '') ?>">
        </label>

        <label>
            <?= _('Beschreibung') ?>
            <textarea name="description"><?= htmlReady($schedule->description ?: '') ?></textarea>
        </label>
    </fieldset>

    <fieldset>
        <legend><?= _('Aufgabe') ?></legend>
        <table class="default cron-task">
            <colgroup>
                <col width="300px">
                <col width="150px">
                <col>
            </colgroup>
            <thead>
                <tr>
                    <th><?= _('Klassenname') ?></th>
                    <th><?= _('Name') ?></th>
                    <th><?= _('Beschreibung') ?></th>
                </tr>
            </thead>
        <? foreach ($tasks as $task): ?>
            <? if (!$schedule->isNew() && $task->task_id != $schedule->task_id) continue; ?>
            <tbody <? if (!$schedule->isNew() && $task->task_id === $schedule->task_id) echo 'class="selected"'; ?>>
                <tr>
                    <td>
                        <label for="task-<?= $task->task_id ?>">
                            <? if ($schedule->isNew()): ?>
                            <input required type="radio" name="task_id"
                                   id="task-<?= $task->task_id ?>"
                                   value="<?= $task->task_id ?>"
                                   <? if ($task->task_id === $schedule->task_id) echo 'checked'; ?>>
                            <? endif; ?>

                            <?= htmlReady($task->class) ?>
                        </label>
                    </td>
                    <td>
                        <label for="task-<?= $task->task_id ?>"><?= htmlReady($task->name) ?: '&nbsp;' ?></label>
                    </td>
                    <td>
                        <label for="task-<?= $task->task_id ?>"><?= htmlReady($task->description) ?: '&nbsp;' ?></label>
                    </td>
                </tr>
            <? if (count($task->parameters) > 0): ?>
                <tr>
                    <td colspan="3">
                        <div class="parameters">
                            <?= $this->render_partial('admin/cronjobs/schedules/parameters', compact('task', 'schedule')) ?>
                        </div>
                    </td>
                </tr>
            <? endif; ?>
            </tbody>
        <? endforeach; ?>
        </table>
    </fieldset>

    <fieldset>
        <legend><?= _('Zeitplan') ?></legend>

        <label>
            <input type="radio" name="type" value="periodic"
                   <? if ($schedule->type === 'periodic') echo 'checked'; ?>>
            <?= _('Wiederholt') ?>
        </label>

        <section>
            <table class="default">
                <colgroup>
                    <col width="20%">
                    <col width="20%">
                    <col width="20%">
                    <col width="20%">
                    <col width="20%">
                </colgroup>
                <thead>
                    <tr>
                        <th>
                            <?= _('Minute') ?>
                        </th>
                        <th>
                            <?= _('Stunde') ?>
                        </th>
                        <th>
                            <?= _('Tag') ?>
                        </th>
                        <th>
                            <?= _('Monat') ?>
                        </th>
                        <th>
                            <?= _('Wochentag') ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="cron-item">
                            <select name="periodic[minute][type]" id="minute" class="size-s">
                                <option value="" title="<?= _('beliebig') ?>">*</option>
                                <option value="once" <? if ($schedule->minute !== null && $schedule->minute >= 0) echo 'selected'; ?>>
                                    min
                                </option>
                                <option value="periodic" <? if ($schedule->minute < 0) echo 'selected'; ?>>
                                    */min
                                </option>
                            </select>
                            <input type="number" name="periodic[minute][value]" value="<?= abs($schedule->minute) ?>">
                        </td>
                        <td class="cron-item">
                            <select name="periodic[hour][type]" id="hour" class="size-s">
                                <option value="" title="<?= _('beliebig') ?>">*</option>
                                <option value="once" <? if ($schedule->hour !== null && $schedule->hour >= 0) echo 'selected'; ?>>
                                    hour
                                </option>
                                <option value="periodic" <? if ($schedule->hour < 0) echo 'selected'; ?>>
                                    */hour
                                </option>
                            </select>
                            <input type="number" name="periodic[hour][value]" value="<?= abs($schedule->hour) ?>">
                        </td>
                        <td class="cron-item">
                            <select name="periodic[day][type]" id="day" class="size-s">
                                <option value="" title="<?= _('beliebig') ?>">*</option>
                                <option value="once" <? if ($schedule->day !== null && $schedule->day >= 0) echo 'selected'; ?>>
                                    day
                                </option>
                                <option value="periodic" <? if ($schedule->day < 0) echo 'selected'; ?>>
                                    */day
                                </option>
                            </select>
                            <input type="number" name="periodic[day][value]" value="<?= abs($schedule->day) ?>">
                        </td>
                        <td class="cron-item">
                            <select name="periodic[month][type]" id="month" class="size-s">
                                <option value="" title="<?= _('beliebig') ?>">*</option>
                                <option value="once" <? if ($schedule->month !== null && $schedule->month >= 0) echo 'selected'; ?>>
                                    month
                                </option>
                                <option value="periodic" <? if ($schedule->month < 0) echo 'selected'; ?>>
                                    */month
                                </option>
                            </select>
                            <input type="number" name="periodic[month][value]" value="<?= abs($schedule->month) ?>" class="size-s">
                        </td>
                        <td>
                            <section>
                                <select name="periodic[day_of_week][value]" id="day_of_week" class="size-s">
                                    <option value=""><?= _('*') ?></option>
                                <? foreach ($days_of_week as $index => $label): ?>
                                    <option value="<?= $index ?>" <? if ($schedule->day_of_week === $index) echo 'selected'; ?>>
                                        <?= $index ?> (<?= $label ?>)
                                    </option>
                                <? endforeach; ?>
                                </select>
                            </section>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <label>
            <input type="radio" name="type" value="once"
                   <? if ($schedule->type === 'once') echo 'checked'; ?>>
            <?= _('Einmalig') ?>
        </label>

        <label class="col-1">
            <?= _('Datum') ?>
            <input type="text" name="once[date]" class="has-date-picker size-s"
                   value="<? if ($schedule->type === 'once' && $schedule->next_execution) echo date('d.m.Y', $schedule->next_execution); ?>">
        </label>

        <label class="col-1">
            <?= _('Uhrzeit') ?>
            <input type="text" name="once[time]" class="has-time-picker size-s"
                   value="<? if ($schedule->type === 'once' && $schedule->next_execution) echo date('H:i', $schedule->next_execution) ?>">
        </label>
    </fieldset>

    <footer class="buttons">
        <?= Button::createAccept(_('Speichern'), 'store') ?>
        <?= LinkButton::createCancel('Abbrechen', $controller->indexURL($page)) ?>
    </footer>
</form>
