<p><?= _('Sie können hier einen Teil der Logging-Funktionen direkt verändern.') ?></p>

<table class="default">
    <thead>
        <tr>
            <th><?= _('Name') ?></th>
            <th><?= _('Beschreibung') ?></th>
            <th><?= _('Template') ?></th>
            <th><?= _('Anzahl') ?></th>
            <th><?= _('Aktiv?') ?></th>
            <th><?= _('Ablaufzeit') ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($log_actions as $log_action): ?>
        <tr>
            <td><?= htmlReady($log_action['name']) ?></td>
            <td>
                <?= htmlReady($log_action['description']) ?>
            </td>
            <td>
                <?= htmlReady($log_action['info_template']) ?>
            </td>
            <td>
                <?= $log_action['log_count'] ?>
            </td>
            <td>
            <? if ($log_action['active']): ?>
                <?= Icon::create('accept', Icon::ROLE_STATUS_GREEN) ?>
            <? else: ?>
                <?= Icon::create('decline', Icon::ROLE_STATUS_RED) ?>
            <? endif ?>
            </td>
            <td style="white-space: nowrap;">
            <? if ($log_action['expires'] > 0): ?>
                <?= $log_action['expires'] / 86400 ?> <?= _('Tage') ?>
            <? else: ?>
                <?= Icon::create('decline', Icon::ROLE_STATUS_RED) ?>
            <? endif ?>
            </td>
            <td>
                <a href="<?= $controller->edit($log_action['action_id']) ?>" data-dialog="size=auto">
                    <?= Icon::create('edit') ?>
                </a>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
