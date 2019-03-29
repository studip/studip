<? use Studip\Button, Studip\LinkButton; ?>

<form action="<?= $controller->filter() ?>" method="post" class="default cronjob-filters">
    <fieldset>
        <legend>
            <?= _('Darstellung einschränken') ?>
            <? if ($pagination->getTotal() != $total): ?>
                <?= sprintf(_('Passend: %u von %u Cronjobs'), $pagination->getTotal(), $total) ?>
            <? endif; ?>
        </legend>
        <label class="col-2">
            <?= _('Typ') ?>
            <select name="filter[type]" id="type" class="submit-upon-select">
                <option value=""><?= _('Alle Cronjobs anzeigen') ?></option>
                <option value="once" <? if ($filter['type'] === 'once') echo 'selected'; ?>>
                    <?= _('Nur einmalige Cronjobs anzeigen') ?>
                </option>
                <option value="periodic" <? if ($filter['type'] === 'periodic') echo 'selected'; ?>>
                    <?= _('Nur regelmässige Cronjobs anzeigen') ?>
                </option>
            </select>
        </label>
        <label class="col-2">
            <?= _('Aufgabe') ?>
            <select name="filter[task_id]" id="task_id" class="submit-upon-select">
                <option value=""><?= _('Alle Cronjobs anzeigen') ?></option>
                <? foreach ($tasks as $task): ?>
                    <option value="<?= $task->task_id ?>" <? if ($filter['task_id'] === $task->task_id) echo 'selected'; ?>>
                        <?= htmlReady($task->name) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>
        <label class="col-2">
            <?= _('Status') ?>
            <select name="filter[status]" id="status" class="submit-upon-select">
                <option value=""><?= _('Alle Cronjobs anzeigen') ?></option>
                <option value="active" <? if ($filter['status'] === 'active') echo 'selected'; ?>>
                    <?= _('Nur aktive Cronjobs anzeigen') ?>
                </option>
                <option value="inactive" <? if ($filter['status'] === 'inactive') echo 'selected'; ?>>
                    <?= _('Nur deaktivierte Cronjobs anzeigen') ?>
                </option>
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
                $controller->filterURL(),
                ['title' => _('Filter zurücksetzen')]
            ) ?>
        <? endif; ?>
    </footer>
</form>


<form class="default" action="<?= $controller->bulk($pagination->getCurrentPage()) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>

<table class="default cronjobs">
    <colgroup>
        <col width="20px">
        <col>
        <col width="40px">
        <col width="100px">
        <col width="30px">
        <col width="30px">
        <col width="30px">
        <col width="30px">
        <col width="30px">
        <col width="90px">
    </colgroup>
    <thead>
        <tr>
            <th>
                <input type="checkbox" name="all" value="1"
                       data-proxyfor=":checkbox[name='ids[]']"
                       data-activates=".cronjobs select[name=action]">
            </th>
            <th><?= _('Cronjob') ?></th>
            <th><?= _('Aktiv') ?></th>
            <th><?= _('Typ') ?></th>
            <th colspan="5"><?= _('Ausführung') ?></th>
            <th><?= _('Optionen') ?></th>
        </tr>
    </thead>
    <tbody>
<? for ($i = 0; $i < $pagination->getPerPage(); $i += 1): ?>
    <? if (!isset($schedules[$i])): ?>
        <tr class="empty">
            <td colspan="10">&nbsp;</td>
        </tr>
    <? else: ?>
        <tr id="job-<?= $schedules[$i]->schedule_id ?>" <? if (!$schedules[$i]->task->active) echo 'class="inactivatible"'; ?>>
            <td style="text-align: center">
                <input type="checkbox" name="ids[]" value="<?= $schedules[$i]->schedule_id ?>">
            </td>
            <td><?= htmlReady($schedules[$i]->title ?: $schedules[$i]->task->name) ?></td>
            <td style="text-align: center;">
            <? if (!$schedules[$i]->task->active): ?>
                <?= Icon::create('checkbox-unchecked', Icon::ROLE_INACTIVE)->asImg(['title' => _('Cronjob kann nicht aktiviert werden, da die zugehörige '.'Aufgabe deaktiviert ist.')]) ?>
            <? elseif ($schedules[$i]->active): ?>
                <a href="<?= $controller->deactivate($schedules[$i], $pagination->getCurrentPage()) ?>" data-behaviour="ajax-toggle">
                    <?= Icon::create('checkbox-checked')->asImg(['title' => _('Cronjob deaktivieren')]) ?>
                </a>
            <? else: ?>
                <a href="<?= $controller->activate($schedules[$i], $pagination->getCurrentPage()) ?>" data-behaviour="ajax-toggle">
                    <?= Icon::create('checkbox-unchecked')->asImg(['title' => _('Cronjob aktivieren')]) ?>
                </a>
            <? endif; ?>
            </td>
            <td><?= ($schedules[$i]->type === 'once') ? _('Einmalig') : _('Regelmässig') ?></td>
        <? if ($schedules[$i]->type === 'once'): ?>
            <td colspan="5">
                <?= strftime('%x %R', $schedules[$i]->next_execution) ?>
            </td>
        <? else: ?>
            <?= $this->render_partial('admin/cronjobs/schedules/periodic-schedule', $schedules[$i]->toArray() + ['display' => 'table-cells']) ?>
        <? endif; ?>
            <td style="text-align: right">
                <a data-dialog href="<?= $controller->display($schedules[$i]) ?>">
                    <?= Icon::create('admin')->asImg(['title' => _('Cronjob anzeigen')]) ?>
                </a>
                <a href="<?= $controller->edit($schedules[$i], $pagination->getCurrentPage()) ?>">
                    <?= Icon::create('edit')->asImg(['title' => _('Cronjob bearbeiten')]) ?>
                </a>
                <a href="<?= $controller->link_for('admin/cronjobs/logs/schedule', $schedules[$i]) ?>">
                    <?= Icon::create('log')->asImg(['title' => _('Log anzeigen')]) ?>
                </a>
                <a href="<?= $controller->cancel($schedules[$i], $pagination->getCurrentPage()) ?>">
                    <?= Icon::create('trash')->asImg(['title' => _('Cronjob löschen')]) ?>
                </a>
            </td>
        </tr>
    <? endif; ?>
<? endfor; ?>
    </tbody>
</table>

    <footer>
        <select name="action" data-activates=".cronjobs button[name=bulk]">
            <option value="">- <?= _('Aktion auswählen') ?> -</option>
            <option value="activate"><?= _('Aktivieren') ?></option>
            <option value="deactivate"><?= _('Deaktivieren') ?></option>
            <option value="cancel"><?= _('Löschen') ?></option>
        </select>
        <?= Button::createAccept(_('Ausführen'), 'bulk') ?>
        <section style="float: right">
            <?= $pagination->asLinks(function ($page) use ($controller) {
                return $controller->index($page);
            }) ?>
        </section>
    </footer>
</form>
