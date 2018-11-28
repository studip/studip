<table class="default">
    <caption>
        <?= _('Vorhandene Rollen') ?>
    </caption>
    <thead>
        <tr>
            <th><?= _('Name') ?></th>
            <th style="text-align: right;"><?= _('Benutzer') ?></th>
            <th style="text-align: right;"><?= _('Plugins') ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($roles as $role): ?>
            <? $role_id = $role->getRoleid() ?>
            <tr>
                <td>
                    <a href="<?= $controller->url_for('admin/role/show_role', $role_id) ?>">
                        <?= htmlReady($role->getRolename()) ?>
                        <? if ($role->getSystemtype()): ?>[<?= _('Systemrolle') ?>]<? endif ?>
                    </a>
                </td>
                <td style="text-align: right;">
                    <?= $stats[$role_id]['users'] ?>
                </td>
                <td style="text-align: right;">
                    <?= $stats[$role_id]['plugins'] ?>
                </td>
                <td class="actions">
                    <? if (!$role->getSystemtype()): ?>
                        <a href="<?= $controller->url_for('admin/role/ask_remove_role', $role_id) ?>">
                            <?= Icon::create('trash', 'clickable', ['title' => _('Rolle löschen')])->asImg() ?>
                        </a>
                    <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
    </tbody>
</table>
