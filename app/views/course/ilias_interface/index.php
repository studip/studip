<form method="post">
<? foreach($ilias_list as $ilias_index => $ilias) : ?>
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
            <td><?=Icon::create('learnmodule', Icon::ROLE_CLICKABLE, [
                            'title'        => $module->getModuleTypeName()
                            ])
            ?></td>
            <td><a href="<?= $controller->url_for($module->getRoute('view_course'))?>" data-dialog="size=auto"><?=$module->getTitle()?></a></td>
            <td><?=$module->getModuleTypeName()?></td>
                <td class="actions">
                    <? $actionMenu = ActionMenu::get() ?>
                    <? $actionMenu->addButton(
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
            <td colspan="3">
                <?= _('Es sind keine Lernobjekte mit dieser Veranstaltung verknüpft.')?>
            </td>
        </tr>
    <? else : ?>
        <tr>
            <td colspan="3">
                <a href="<?= $controller->url_for('my_ilias_accounts/redirect/'.$ilias_index.'/start/'.$courses[$ilias_index].'/crs')?>" target="_blank"><?= sprintf(_('Kurs in %s'), $ilias->getName())?></a>
            </td>
        </tr>
    <? endif ?>
        </tbody>
    </table>
<? endforeach ?>
</form>