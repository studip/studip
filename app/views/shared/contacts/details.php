<td colspan="10">
    <? if (!sizeof($relations)) : ?>
        <?= _('Diese Person wurde noch nicht referenziert.') ?>
    <? else : ?>
        <? foreach ($relations as $object_type => $relations) : ?>
            <? if (!$object_type) : continue; endif; ?>
            <table class="default sortable-table" style="margin-top: 10px;" data-sortlist="[[0, 0]]">
                <colgroup>
                    <? if($object_type === 'Studiengang'): ?>
                        <col width="50%">
                        <col width="20%">
                    <? else: ?>
                        <col width="70%">
                    <? endif; ?>
                        <col width="20%">
                        <col width="5%">
                    </colgroup>
                <caption>
                    <?= htmlReady($object_type::getClassDisplayName()) ?>
                </caption>
                <thead>
                    <tr class="sortable">
                    <? if($object_type === 'Studiengang'): ?>
                        <th data-sorter="text"><?= _('Name'); ?></th>
                        <th data-empty="top" data-sorter="text"><?= _('Ansprechpartnertyp'); ?></th>
                    <? else: ?>
                        <th data-sorter="text"><?= _('Name'); ?></th>
                    <? endif; ?>
                        <th data-sorter="text"><?= _('Kategorie'); ?></th>
                        <th data-sorter="false" style="width: 5%; text-align: right;"><?= _('Aktionen') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($relations as $range_entries) : ?>
                        <? foreach ($range_entries as $rel) : ?>
                            <? $object_name = htmlReady($object_type::find($rel['range_id'])->getDisplayName()); ?>
                                <tr>
                                    <td data-sort-value="<?= $object_name ?>">
                                        <a href="<?= $this->controller->url_for('shared/contacts/dispatch', mb_strtolower($object_type), $rel['range_id']) ?>">
                                            <?= $object_name ?>
                                        </a>
                                    </td>
                                    <? if($object_type === 'Studiengang'): ?>
                                    <td>
                                        <?= htmlReady($GLOBALS['MVV_CONTACTS']['TYPE']['values'][$rel['type']]['name']); ?>
                                    </td>
                                    <? endif; ?>
                                    <td>
                                        <?= htmlReady($rel->getCategoryDisplayname()); ?>
                                    </td>
                                    <td class="actions">
                                    <?
                                        $actions = ActionMenu::get();
                                        $actions->addLink(
                                            $controller->url_for('shared/contacts/add_ansprechpartner',$origin, $rel['range_type'], $rel['range_id'], $rel['contact_id'], $rel['category']),
                                            _('Ansprechpartner bearbeiten'),
                                            Icon::create('edit'),
                                            ['data-dialog' => 'size=auto']
                                        );
                                        $actions->addLink(
                                            $controller->url_for('shared/contacts/delete_range', $rel['range_id'], $rel['contact_id'], $rel['category']),
                                            _('Ansprechpartner-Zuordnung löschen'),
                                            Icon::create('trash'),
                                            [
                                                'data-confirm' => _('Wollen Sie die Zuordnung des Ansprechpartners wirklich entfernen?'),
                                                'data-dialog' => 'size=auto'
                                            ]
                                        );
                                        echo $actions;
                                    ?>
                                    </td>
                                </tr>
                        <? endforeach; ?>
                    <? endforeach; ?>
                </tbody>
            </table>
        <? endforeach; ?>
    <? endif; ?>
</td>
