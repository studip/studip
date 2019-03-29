<table class="default">
    <caption>
        <?= _('Im Pluginverzeichnis vorhandene Plugins registrieren') ?>
    </caption>
    <thead>
        <tr>
            <th><?= _('Name') ?></th>
            <th><?= _('Pluginklasse') ?></th>
            <th><?= _('Version') ?></th>
            <th><?= _('Ursprung') ?></th>
            <th><?= _('Registrieren') ?></th>
        </tr>
    </thead>
    <tbody>
    <? if (!$unknown_plugins): ?>
        <tr>
            <td colspan="5">
                <?= _('Es sind keine nicht registrierten Plugins vorhanden') ?>
            </td>
        </tr>
    <? endif; ?>
    <? foreach ($unknown_plugins as $n => $plugin): ?>
        <tr>
            <td><?= htmlReady($plugin['pluginname']) ?></td>
            <td><?= htmlReady($plugin['pluginclassname']) ?></td>
            <td><?= htmlReady($plugin['version']) ?></td>
            <td><?= htmlReady($plugin['origin']) ?></td>
            <td class="actions">
                <form action="<?= $controller->link_for('admin/plugin/register/' . $n) ?>" method="post">
                    <?= CSRFProtection::tokenTag() ?>
                    <?= Icon::create('install')->asInput([
                        'title' => _('Plugin registrieren'),
                        'class' => 'middle',
                        'name'  => 'install',
                    ]) ?>
                </form>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
