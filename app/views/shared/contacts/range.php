<div id="messagebox-container">
<? if (Request::isXhr()) : ?>
    <? foreach (PageLayout::getMessages() as $messagebox) : ?>
        <?= $messagebox ?>
    <? endforeach ?>
<? endif; ?>
</div>
<table id="mvv_contacts" class="default sortable-table" data-sortlist="[[0, 0]]">
    <caption>
        <span class="actions">
            <a href="<?= $controller->url_for('shared/contacts/add_ansprechpartner', 'range', $range_type, $range_id);?>" data-dialog="size=auto">
                <?= Icon::create('headache+add', Icon::ROLE_CLICKABLE, ['title' => _('Ansprechpartner hinzufügen')]); ?>
            </a>
            <a href="<?= $controller->url_for('shared/contacts/sort', $range_id);?>" data-dialog="size=auto">
                <?= Icon::create('arr_2up', Icon::ROLE_CLICKABLE, ['title' => _('Reihenfolge der Ansprechpartner ändern')]); ?>
            </a>
        </span>
    </caption>
    <thead>
        <tr class="sortable">
            <th data-sorter="digit"><?= _('Pos.'); ?></th>
            <th data-sorter="text"><?= _('Name/Institution'); ?></th>
        <? if($range_type !== 'Modul'): ?>
            <th data-empty="top" data-sorter="text"><?= _('Ansprechpartner-Typ'); ?></th>
        <? endif; ?>
            <th data-sorter="text"><?= _('Kategorie'); ?></th>
            <th data-sorter="digit"><?= _('Zuordnungen'); ?></th>
            <th data-sorter="false" style="width: 5%; text-align: right;"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
<? if($contacts): ?>
    <tbody>
    <? foreach($contacts as $mvv_contact): ?>

        <tr>
            <td><?= htmlReady($mvv_contact->position); ?></td>
            <td><?= htmlReady($mvv_contact->name) ?></td>
        <? if($range_type !== 'Modul'): ?>
            <td><?= htmlReady($GLOBALS['MVV_CONTACTS']['TYPE']['values'][$mvv_contact->type]['name']) ?></td>
        <? endif; ?>
            <td><?= htmlReady($mvv_contact->getCategoryDisplayname()); ?></td>
            <td ><?= htmlReady($mvv_contact->count_relations); ?></td>
            <td class="actions">
            <?
                $actions = ActionMenu::get();
                $actions->addLink(
                    $controller->url_for('shared/contacts/add_ansprechpartner', 'range', $mvv_contact->range_type, $mvv_contact->range_id, $mvv_contact->contact_id, $mvv_contact->category),
                    _('Ansprechpartner bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                );
                $actions->addLink(
                    $controller->url_for('shared/contacts/delete_range', $mvv_contact->range_id, $mvv_contact->contact_id, $mvv_contact->category),
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
        <? if ($range_id == $mvv_contact->range_id && $contact_id == $mvv_contact->contact_id) : ?>
            <tr class="loaded-details nohover">
                <?= $this->render_partial('shared/contacts/details', compact('mvv_contact')) ?>
            </tr>
        <? endif; ?>

    <? endforeach; ?>
    </tbody>
<? endif; ?>
</table>
<script type="text/javascript">
jQuery(function ($) {
    $(document).on('dialog-close', function(event) {
        if ($('div.ui-dialog.studip-confirmation').length) {
            STUDIP.MVV.Contact.reload_contacttable('<?= htmlReady($range_id) ?>', '<?= htmlReady($range_type) ?>');
        }
    });
});
</script>
