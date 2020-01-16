<? if ($properties): ?>
    <table class="default sortable-table" id="Resources_PropertyTable"
           data-sortlist="[[0, 0]]">
        <thead>
            <tr>
                <th data-sort="text"><?= _('Name') ?></th>
                <th data-sort="text"><?= _('Angezeigter Name') ?></th>
                <th data-sort="text"><?= _('Typ') ?></th>
                <th><?= _('Minimale Rechtestufe für Änderungen') ?></th>
                <th><?= _('Mögliches Suchkriterium') ?></th>
                <th><?= _('Verwendung in Kategorien') ?></th>
                <th class="actions"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($properties as $property): ?>
                <tr>
                    <td><?= htmlReady($property->name) ?></td>
                    <td>
                        <?= htmlReady(
                            trim($property->display_name)
                            ? $property->display_name
                            : $property->name
                        ) ?>
                    </td>
                    <td><?= htmlReady($property->type) ?></td>
                    <td><?= htmlReady($property->write_permission_level) ?></td>
                    <td><?= $property->searchable ? _('ja') : _('nein') ?></td>
                    <td>
                        <? if (is_array($categories[$property->id])): ?>
                            <?= htmlReady(implode(', ', $categories[$property->id])) ?>
                        <? endif ?>
                    </td>
                    <td class="actions">
                        <form method="post" class="default"
                            action="<?= $controller->url_for('resources/property/delete/' . $property->id)?>">
                            <?= CSRFProtection::tokenTag() ?>
                            <a href="<?=$controller->url_for('resources/property/edit/' . $property->id)?>"
                               data-dialog="size=auto">
                                <?= Icon::create('edit')->asImg(
                                    [
                                        'title' => _('Bearbeiten')
                                    ]
                                ) ?>
                            </a>
                            <? if (!$property->system): ?>
                                <?= Icon::create('trash')->asInput(
                                    [
                                        'title' => _('Löschen'),
                                        'data-confirm' => sprintf(
                                            _('Soll die Eigenschaft "%s" wirklich gelöscht werden?'
                                            ),
                                            htmlReady($property->name)
                                        )
                                    ]
                                ) ?>
                            <? endif ?>
                        </form>
                    </td>
                </tr>
            <? endforeach ?>
        </tbody>
    </table>
<? else: ?>
    <?= MessageBox::info(
        _('Es sind keine Eigenschaften definiert!')
    ) ?>
<? endif ?>
