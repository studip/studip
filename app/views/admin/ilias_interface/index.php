 <form method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption>
            <?= _('Angebundene ILIAS-Installationen') ?>
            <span class="actions">
                <a href="<?= $controller->url_for('admin/ilias_interface/edit_server/new') ?>" data-dialog="size=auto">
                    <?= Icon::create('add')->asImg(tooltip2(_('Neue ILIAS-Installation hinzufügen'))) ?>
                </a>
            </span>
        </caption>
        <colgroup>
            <col style="width: 10%">
            <col style="width: 60%">
            <col style="width: 10%">
            <col style="width: 10%">
            <col style="width: 10%">
        </colgroup>
        <thead>
            <th><?= _('Aktiv') ?></th>
            <th><?= _('Name') ?></th>
            <th><?= _('Index') ?></th>
            <th><?= _('Version') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </thead>
        <tbody>
        <? foreach ($ilias_configs as $ilias_index => $ilias_config) : ?>
            <tr id="ilias-<?= htmlReady($ilias_index)?>">
                <td>
                    <? if ($ilias_config['is_active']) {
                        $text = _('Diese ILIAS-Installation ist aktiv. Klicken Sie hier, um sie zu deaktivieren.');
                        $img  = 'checkbox-checked';
                        $cmd  = 'deactivate';
                    } else {
                        $text = _('Diese ILIAS-Installation ist inaktiv. Klicken Sie hier, um sie zu aktivieren.');
                        $img  = 'checkbox-unchecked';
                        $cmd  = 'activate';
                    } 
                    ?>
                    <a href="<?= $controller->url_for('admin/ilias_interface/'.$cmd.'/'.$ilias_index) ?>">
                        <?= Icon::create($img, 'clickable', ['title' => $text])->asImg() ?>
                    </a>
                </td>
                <td><?= htmlReady($ilias_config['name']) ?></td>
                <td><?= htmlReady($ilias_index) ?></td>
                <td><?= htmlReady($ilias_config['version']) ?></td>
                <td class="actions">
                    <? $actionMenu = ActionMenu::get() ?>
                    <? $actionMenu->addLink(
                        $controller->url_for("admin/ilias_interface/edit_server/$ilias_index"),
                        _('Servereinstellungen bearbeiten'),
                        Icon::create('edit'),
                        ['data-dialog' => 'size=auto']
                    ) ?>
                    <? $actionMenu->addLink(
                        $controller->url_for("admin/ilias_interface/edit_content/$ilias_index"),
                        _('Inhaltseinstellungen bearbeiten'),
                        Icon::create('edit'),
                        ['data-dialog' => 'size=auto']
                    ) ?>
                    <? $actionMenu->addLink(
                        $controller->url_for("admin/ilias_interface/edit_permissions/$ilias_index"),
                        _('Berechtigungen bearbeiten'),
                        Icon::create('edit'),
                        ['data-dialog' => 'size=auto']
                    ) ?>

                    <? $actionMenu->addButton(
                            'delete_config',
                            _('Konfiguration löschen'),
                            Icon::create('trash', Icon::ROLE_CLICKABLE, [
                                'title'        => _('Konfiguration löschen'),
                                'formaction'   => $controller->url_for("admin/ilias_interface/delete/$ilias_index"),
                                'data-confirm' => sprintf(
                                    sprintf(_('Soll die ILIAS-Installation "%s" wirklich entfernt werden?'),
                                    htmlReady($ilias_config['name']))
                                ),
                            ])
                    ) ?>
                    <?= $actionMenu->render() ?>
                </td>
            </tr>
        <? endforeach; ?>

        <? if (!$ilias_configs): ?>
            <tr>
                <td colspan="5" style="text-align: center">
                    <?= _('Es ist keine ILIAS-Installation eingerichtet.') ?>
                </td>
            </tr>
        <? endif ?>
        </tbody>
    </table>
</form>