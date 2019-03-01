<form method="post">
<? foreach($ilias_list as $ilias_index => $ilias) : ?>
    <? if (!count($ilias->getCourseModules()) && !$courses[$ilias_index] && !$edit_permission) continue; ?>
    <? if ($anker_target == $ilias_index) : ?>
        <a name='anker'></a>
    <? endif?>
    <table class="default">
        <caption>
            <?= sprintf(_('Lernobjekte in %s'), htmlReady($ilias->getName()))?>
        </caption>
        <colgroup>
            <col style="width: 5%">
            <col style="width: 65%">
            <col style="width: 20%">
            <col style="width: 10%">
        </colgroup>
        <thead>
            <th></th>
            <th><?= _('Name') ?></th>
            <th><?= _('Typ') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </thead>
        <tbody>
    <? if (count($ilias->getCourseModules())) : ?>
        <? foreach ($ilias->getCourseModules() as $module_id => $module) : ?>
        <tr>
            <td><?=Icon::create('learnmodule', $module->is_offline ? Icon::ROLE_INACTIVE : Icon::ROLE_INFO, [
                            'title'        => $module->getModuleTypeName()
                            ])
            ?></td>
            <? if ($module->is_offline) : ?>
            <td><?=$module->getTitle()?> <?=_('(offline)')?></td>
            <? else : ?>
            <td><a href="<?= $controller->url_for($module->getRoute('view_course'))?>" data-dialog="size=auto"><?=$module->getTitle()?></a></td>
            <? endif ?>
            <td><?=$module->getModuleTypeName()?></td>
                <td class="actions">
                    <? $actionMenu = ActionMenu::get() ?>
                    <? if (! $module->is_offline) $actionMenu->addButton(
                            'view',
                            _('Info'),
                            Icon::create('info-circle', Icon::ROLE_CLICKABLE, [
                                'title'        => _('Info'),
                                'formaction'   => $controller->url_for($module->getRoute('view_course')),
                                'data-dialog'  => 'size=auto'
                            ])
                    ) ?>
                    <? if ($module->isAllowed('start')) $actionMenu->addButton(
                            'start',
                            _('In ILIAS anzeigen'),
                            Icon::create('play', Icon::ROLE_CLICKABLE, [
                                'title'        => _('In ILIAS anzeigen'),
                                'formaction'   => $controller->url_for($module->getRoute('start')),
                                'target'       => '_blank',
                                'rel'          => 'noopener noreferrer'
                            ])
                    ) ?>
                    <? if ($module->isAllowed('edit')) $actionMenu->addButton(
                            'edit',
                            _('In ILIAS bearbeiten'),
                            Icon::create('learnmodule+edit', Icon::ROLE_CLICKABLE, [
                                'title'        => _('In ILIAS bearbeiten'),
                                'formaction'   => $controller->url_for($module->getRoute('edit')),
                                'target'       => '_blank',
                                'rel'          => 'noopener noreferrer'
                            ])
                    ) ?>
                    <? if ($edit_permission) $actionMenu->addButton(
                            'remove',
                            _('Entfernen'),
                            Icon::create('learnmodule+decline', Icon::ROLE_CLICKABLE, [
                                'title'        => _('Entfernen'),
                                'formaction'   => $controller->url_for($module->getRoute('remove')),
                                'target'       => '_blank',
                                'rel'          => 'noopener noreferrer'
                            ])
                    ) ?>
                    <?= $actionMenu->render() ?>
                </td>
        </tr>
        <? endforeach ?>
    <? elseif (!$courses[$ilias_index]) : ?>
        <tr>
            <td colspan="4">
                <?= _('Es sind keine Lernobjekte mit dieser Veranstaltung verknüpft.')?>
            </td>
        </tr>
    <? else : ?>
        <tr>
            <td><?=Icon::create('learnmodule', Icon::ROLE_INFO, [
                            'title'        => _('ILIAS-Kurs')
                            ])
            ?></td>
            <td>
                <a href="<?= $controller->url_for('my_ilias_accounts/redirect/'.$ilias_index.'/start/'.$courses[$ilias_index].'/crs')?>" target="_blank"><?= sprintf(_('Kurs in %s'), $ilias->getName())?></a>
            </td>
            <td><?=_('ILIAS-Kurs')?></td>
                <td class="actions">
                    <? $actionMenu = ActionMenu::get() ?>
                    <? $actionMenu->addButton(
                            'start',
                            _('In ILIAS anzeigen'),
                            Icon::create('play', Icon::ROLE_CLICKABLE, [
                                'title'        => _('In ILIAS anzeigen'),
                                'formaction'   => $controller->url_for('my_ilias_accounts/redirect/'.$ilias_index.'/start/'.$courses[$ilias_index].'/crs'),
                                'target'       => '_blank',
                                'rel'          => 'noopener noreferrer'
                            ])
                    ) ?>
                    <? if ($edit_permission) $actionMenu->addButton(
                            'remove',
                            _('Entfernen'),
                            Icon::create('learnmodule+decline', Icon::ROLE_CLICKABLE, [
                                'title'        => _('Entfernen'),
                                'formaction'   => $controller->url_for('course/ilias_interface/remove_course/'.$ilias_index.'/'.$courses[$ilias_index]),
                                'data-confirm' => sprintf(_('Verknüpfung zum Kurs in %s entfernen? Hierdurch werden auch die Verknüpfungen zu allen Objekten innerhalb des Kurses entfernt.'), $ilias->getName()),
                                'target'       => '_blank',
                                'rel'          => 'noopener noreferrer'
                            ])
                    ) ?>
                    <?= $actionMenu->render() ?>
                </td>
        </tr>
    <? endif ?>
        </tbody>
    </table>
<? endforeach ?>
</form>