<table class="default">
    <caption>
        <?= _('Vorhandene Rollen') ?>
    </caption>
    <thead>
        <tr>
            <th rowspan="2"><?= _('Name') ?></th>
            <th colspan="2" style="text-align: right;">
                <?= _('Benutzer') ?>
            </th>
            <th style="text-align: right;"  rowspan="2"><?= _('Plugins') ?></th>
            <th rowspan="2"></th>
        </tr>
        <tr>
            <th style="text-align: right;">
                <abbr title="<?= _('Direkte Zuweisung') ?>">
                    <?= _('explizit') ?>
                </abbr>
            </th>
            <th style="text-align: right;">
                <abbr title="<?= _('Indirekte Zuweisung durch Berechtigungsstufe') ?>">
                    <?= _('implizit') ?>
                </abbr>
            </th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($roles as $role): ?>
        <? $role_id = $role->getRoleid() ?>
        <tr>
            <td>
                <a href="<?= $controller->link_for("admin/role/show_role/{$role_id}") ?>">
                    <?= htmlReady($role->getRolename()) ?>
                <? if ($role->getSystemtype()): ?>
                    [<?= _('Systemrolle') ?>]
                <? endif ?>
                </a>
            </td>
            <td style="text-align: right;">
                <?= $stats[$role_id]['explicit'] ?>
            </td>
            <td style="text-align: right;">
                <?= $stats[$role_id]['implicit'] ?>
            </td>
            <td style="text-align: right;">
                <?= $stats[$role_id]['plugins'] ?>
            </td>
            <td class="actions">
            <? if (!$role->getSystemtype()): ?>
                <a href="<?= $controller->link_for('admin/role/ask_remove_role', $role_id) ?>">
                    <?= Icon::create('trash')->asImg(tooltip2(_('Rolle löschen'))) ?>
                </a>
            <? endif ?>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
