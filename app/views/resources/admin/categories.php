<? if ($categories): ?>
    <table class="default resource-categories">
        <colspan>
            <col class="icon">
            <col>
            <col>
            <col>
            <col>
            <col class="actions">
        </colspan>
    <thead>
        <tr>
            <th></th>
            <th><?= _('Name') ?></th>
            <th><?= _('Beschreibung') ?></th>
            <th><?= _('Klassenname') ?></th>
            <th><?= _('System-Kategorie') ?></th>
            <th><?= _('Eigenschaften') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($categories as $category): ?>
        <tr>
            <td>
                <img class="small-resource-picture"
                    src="<?= $category->getIconUrl() ?>">
            </td>
            <td><?= htmlReady($category->name) ?></td>
            <td><?= htmlReady($category->description) ?></td>
            <td><?= htmlReady($category->class_name) ?></td>
            <td><?= $category->system ? _('ja') : _('nein') ?></td>
            <td class="properties">
                <? if ($category->property_definitions): ?>
                    <ul>
                        <? foreach ($category->property_definitions as $definition): ?>
                            <li>
                                <? if ($definition->system): ?>
                                    <strong><?= htmlReady($definition) ?></strong>
                                <? else: ?>
                                    <?= htmlReady($definition) ?>
                                <? endif ?>
                                [<?= htmlReady($definition->type) ?>]
                            </li>
                        <? endforeach ?>
                    </ul>
                <? endif ?>
            </td>
            <td class="actions">
                <?
                $actions = ActionMenu::get();
                if ($category->hasResources()) {
                    $actions->addLink(
                        $controller->link_for(
                            'resources/category/show_resources/' . $category->id
                        ),
                        _('Alle Ressourcen anzeigen'),
                        Icon::create('log'),
                        ['data-dialog' => 'size=auto']
                    );
                }
                $actions->addLink(
                    $controller->link_for(
                        'resources/category/edit/' . $category->id
                    ),
                    _('Bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                );
                if ($category->system == '0') {
                    $actions->addLink(
                        $controller->link_for(
                            'resources/category/delete/' . $category->id
                        ),
                        _('Löschen'),
                        Icon::create('trash'),
                        ['data-dialog' => 'size=auto']
                    );
                }
                ?>
                <?= $actions->render() ?>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>
</table>
<? endif ?>
