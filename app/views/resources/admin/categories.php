<? if ($categories): ?>
    <table class="default resource-categories">
        <colspan>
            <col style="width: 20px">
            <col style="width: 50%">
            <col style="width: 30%">
            <col style="width:10%">
            <col class="actions">
        </colspan>
        <thead>
        <tr>
            <th></th>
            <th><?= _('Name') ?></th>
            <th><?= _('Klassenname') ?></th>
            <th><?= _('System-Kategorie') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </tr>
        </thead>
        <tbody>
        <? foreach ($categories as $category): ?>
            <tr>
                <td>
                    <?= $category->getIcon()->asImg(20) ?>
                </td>
                <td><?= htmlReady($category->name) ?></td>
                <td><?= htmlReady($category->class_name) ?></td>
                <td style="text-align: center">
                    <?= Icon::create(
                        sprintf('checkbox-%s', $category->system ? 'checked' : 'unchecked'),
                        Icon::ROLE_INFO
                    ) ?>
                </td>
                <td class="actions">
                    <?php
                    $actions = ActionMenu::get()
                        ->conditionAll($category->hasResources())
                        ->addLink(
                            $controller->url_for(
                                'resources/category/show_resources/' . $category->id
                            ),
                            _('Alle Ressourcen anzeigen'),
                            Icon::create('log'),
                            ['data-dialog' => 'size=auto'])
                        ->addLink(
                            $controller->url_for(
                                'resources/category/details', $category
                            ),
                            _('Details anzeigen'),
                            Icon::create('info-circle'),
                            ['data-dialog' => 'size=auto']
                        )
                        ->conditionAll(true)
                        ->addLink(
                            $controller->url_for(
                                'resources/category/edit/' . $category->id
                            ),
                            _('Bearbeiten'),
                            Icon::create('edit'),
                            ['data-dialog' => 'size=auto'])
                        ->condition($category->system == '0')
                        ->addLink(
                            $controller->url_for(
                                'resources/category/delete/' . $category->id
                            ),
                            _('Löschen'),
                            Icon::create('trash'),
                            ['data-dialog' => 'size=auto']
                        );
                    echo $actions->render();
                    ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
<? endif ?>
