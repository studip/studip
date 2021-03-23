<? use Studip\Button; ?>

<form action="<?= $controller->bulk($pagination->getCurrentPage()) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <table class="default cronjobs sortable-table" data-sortlist="[[1,0]]">
        <colgroup>
            <col style="width: 20px">
            <col style="width: 200px">
            <col>
            <col style="width: 100px">
            <col style="width: 40px">
            <col style="width: 70px">
        </colgroup>
        <thead>
            <tr>
                <th data-sort="false">
                    <input type="checkbox" name="all" value="1"
                           data-proxyfor=":checkbox[name='ids[]']"
                           data-activates=".cronjobs select[name=action]">
                </th>
                <th data-sort="text"><?= _('Aufgabe') ?></th>
                <th data-sort="text"><?= _('Beschreibung') ?></th>
                <th data-sort="text"><?= _('Herkunft') ?></th>
                <th data-sort="htmldata"><?= _('Aktiv') ?></th>
                <th data-sort="false"><?= _('Optionen') ?></th>
            </tr>
        </thead>
        <tbody>
        <? if (count($tasks) === 0): ?>
            <tr class="empty">
                <td colspan="6"><?= _('Keine Einträge vorhanden') ?></td>
            </tr>
        <? endif; ?>
        <? foreach ($tasks as $task): ?>
            <tr id="job-<?= htmlReady($task->id) ?>">
                <td style="text-align: center">
                    <input type="checkbox" name="ids[]" value="<?= htmlReady($task->id) ?>">
                </td>
                <td><?= htmlReady($task->name) ?></td>
                <td><?= htmlReady($task->description) ?></td>
                <td><?= $task->isCore() ? _('Kern') : _('Plugin') ?></td>
                <td style="text-align: center;" data-sort-value="'<?= (int) $task->active ?>'">
                <? if ($task->active): ?>
                    <a href="<?= $controller->deactivate($task, $pagination->getCurrentPage()) ?>" data-behaviour="ajax-toggle">
                        <?= Icon::create('checkbox-checked')->asImg(['title' => _('Aufgabe deaktivieren')]) ?>
                    </a>
                <? else: ?>
                    <a href="<?= $controller->activate($task, $pagination->getCurrentPage()) ?>" data-behaviour="ajax-toggle">
                        <?= Icon::create('checkbox-unchecked')->asImg(['title' => _('Aufgabe aktivieren')]) ?>
                    </a>
                <? endif; ?>
                </td>
                <td style="text-align: right">
                <? if ($task->valid): ?>
                    <a data-dialog href="<?= $controller->execute($task) ?>">
                        <?= Icon::create('play')->asImg(['title' => _('Aufgabe ausführen')]) ?>
                    </a>
                <? endif; ?>
                    <a href="<?= $controller->link_for('admin/cronjobs/logs/task', $task) ?>">
                        <?= Icon::create('log')->asImg(['title' => _('Log anzeigen')]) ?>
                    </a>
                    <?= Icon::create('trash')->asInput([
                        'data-confirm' => _('Wollen Sie die ausgewählte Aufgabe wirklich löschen?'),
                        'formaction'   => $controller->deleteURL($task, $pagination->getCurrentPage()),
                    ]) ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">
                    <select name="action" data-activates=".cronjobs button[name=bulk]">
                        <option value="">- <?= _('Aktion auswählen') ?></option>
                        <option value="activate"><?= _('Aktivieren') ?></option>
                        <option value="deactivate"><?= _('Deaktivieren') ?></option>
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
