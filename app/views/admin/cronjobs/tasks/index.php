<? use Studip\Button; ?>

<form action="<?= $controller->bulk($pagination->getCurrentPage()) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

<table class="default cronjobs">
    <colgroup>
        <col width="20px">
        <col width="200px">
        <col>
        <col width="100px">
        <col width="40px">
        <col width="70px">
    </colgroup>
    <thead>
        <tr>
            <th>
                <input type="checkbox" name="all" value="1"
                       data-proxyfor=":checkbox[name='ids[]']"
                       data-activates=".cronjobs select[name=action]">
            </th>
            <th><?= _('Aufgabe') ?></th>
            <th><?= _('Beschreibung') ?></th>
            <th><?= _('Herkunft') ?></th>
            <th><?= _('Aktiv') ?></th>
            <th><?= _('Optionen') ?></th>
        </tr>
    </thead>
    <tbody>
<? for ($i = 0; $i < $pagination->getPerPage(); $i += 1): ?>
    <? if (!isset($tasks[$i])): ?>
        <tr class="empty">
            <td colspan="6">&nbsp;</td>
        </tr>
    <? else: ?>
        <tr id="job-<?= $tasks[$i]->task_id ?>">
            <td style="text-align: center">
                <input type="checkbox" name="ids[]" value="<?= $tasks[$i]->task_id ?>">
            </td>
            <td><?= htmlReady($tasks[$i]->name) ?></td>
            <td><?= htmlReady($tasks[$i]->description) ?></td>
            <td><?= $tasks[$i]->isCore() ? _('Kern') : _('Plugin') ?></td>
            <td style="text-align: center;">
            <? if ($tasks[$i]->active): ?>
                <a href="<?= $controller->deactivate($tasks[$i], $pagination->getCurrentPage()) ?>" data-behaviour="ajax-toggle">
                    <?= Icon::create('checkbox-checked')->asImg(['title' => _('Aufgabe deaktivieren')]) ?>
                </a>
            <? else: ?>
                <a href="<?= $controller->activate($tasks[$i], $pagination->getCurrentPage()) ?>" data-behaviour="ajax-toggle">
                    <?= Icon::create('checkbox-unchecked')->asImg(['title' => _('Aufgabe aktivieren')]) ?>
                </a>
            <? endif; ?>
            </td>
            <td style="text-align: right">
            <? if ($tasks[$i]->valid): ?>
                <a data-dialog href="<?= $controller->execute($tasks[$i]) ?>">
                    <?= Icon::create('play')->asImg(['title' => _('Aufgabe ausführen')]) ?>
                </a>
            <? endif; ?>
                <a href="<?= $controller->link_for('admin/cronjobs/logs/task', $tasks[$i]) ?>">
                    <?= Icon::create('log')->asImg(['title' => _('Log anzeigen')]) ?>
                </a>
                <a href="<?= $controller->link_for('admin/cronjobs/tasks/delete', $tasks[$i], $pagination->getCurrentPage()) ?>">
                    <?= Icon::create('trash')->asImg(['title' => _('Aufgabe löschen')]) ?>
                </a>
            </td>
        </tr>
    <? endif; ?>
<? endfor; ?>
    </tbody>
</table>
    <footer>
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
    </footer>
</table>
</form>
