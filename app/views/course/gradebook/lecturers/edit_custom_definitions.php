<table class="default">
    <caption>
        <?= _('Manuelle Leistungen definieren') ?>
    </caption>

    <thead>
        <tr class="tablesorter-ignoreRow">
            <th><?= _('Name') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </tr>
    </thead>

    <? if (count($customDefinitions)) { ?>
        <tbody>
            <? foreach ($customDefinitions as $definition) { ?>
                <tr>
                    <td>
                        <?= htmlReady($definition->name) ?>
                    </td>
                    <td class="actions">
                        <?=
                        \ActionMenu::get()
                                   ->addLink(
                                       $controller->url_for(
                                           'course/gradebook/lecturers/edit_custom_definition',
                                           $definition->id
                                       ),
                                       _('Ändern'),
                                       Icon::create('edit'),
                                       ['data-dialog' => 'size=fit']
                                   )
                                   ->addLink(
                                       $controller->url_for(
                                           'course/gradebook/lecturers/delete_custom_definition',
                                           $definition->id
                                       ),
                                       _('Löschen'),
                                       Icon::create('trash'),
                                       ['onclick' => "return STUDIP.Dialog.confirmAsPost('" . _('Wollen Sie die Leistungsdefinition wirklich löschen?') . "', this.href);"]
                                   ) ?>
                    </td>
                </tr>
            <? } ?>
        </tbody>
    <? } else { ?>
        <tbody>
            <tr>
                <td colspan="2">
                    <?= \MessageBox::info(_('Es sind keine manuellen Leistungen definiert.')) ?>
                </td>
        </tbody>
    <? } ?>


    <tfoot class="gradebook-lecturer-custom-definitions-actions">
        <tr>
            <td colspan="2">
                <?= \Studip\LinkButton::createAdd(
                    count($customDefinitions) ? _('Weitere Leistung definieren') : _('Leistung definieren'),
                    $controller->url_for('course/gradebook/lecturers/new_custom_definition'),
                    ['data-dialog' => 'size=fit']
                ) ?>
            </td>
        </tr>
    </tfoot>
</table>
