<div id="messagebox-container">
<? if (Request::isXhr()) : ?>
    <? foreach (PageLayout::getMessages() as $messagebox) : ?>
        <?= $messagebox ?>
    <? endforeach ?>
<? endif; ?>
</div>
<table id="mvv_contacts" class="default collapsable">
    <caption>
        <span class="actions"><? printf('%s Ansprechpartner', $count) ?></span>
    </caption>
    <thead>
        <tr>
            <?= $controller->renderSortLink('shared/contacts/', _('Name/Institution'), 'name', ['style' => 'width: 40%;']) ?>
            <?= $controller->renderSortLink('shared/contacts/', _('Alternative Kontaktmail'), 'alt_mail', ['style' => 'width: 20%;']) ?>
            <?= $controller->renderSortLink('shared/contacts/', _('Status'), 'contact_status', ['style' => 'width: 15%;']) ?>
            <?= $controller->renderSortLink('shared/contacts/', _('Zuordnungen'), 'count_relations', ['style' => 'width: 20%;']) ?>
            <th style="width: 5%; text-align: right;"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
<? if ($contacts) : ?>
    <? foreach ($contacts as $mvv_contact) : ?>
    <? $perm = new MvvPerm($mvv_contact) ?>
    <tbody class="<?= ($contact_id == $mvv_contact->contact_id ? 'not-collapsed' : 'collapsed') ?>">
        <tr class="header-row">
            <td class="toggle-indicator">
                <a class="mvv-load-in-new-row"
                    href="<?= $controller->url_for('shared/contacts/details/index', $mvv_contact->contact_id) ?>"><?= htmlReady($mvv_contact->getContactName()) ?></a>
            </td>
            <td class="dont-hide"><?= htmlReady($mvv_contact->alt_mail); ?></td>
            <td class="dont-hide"><?= htmlReady($GLOBALS['MVV_CONTACTS']['STATUS']['values'][$mvv_contact->contact_status]['name']); ?></td>
            <td class="dont-hide"><?= htmlReady($mvv_contact->count_relations); ?></td>
            <td class="dont-hide actions">
            <?
                $actions = ActionMenu::get();
                if ($perm->haveFieldPerm('ranges', MvvPerm::PERM_CREATE)) {
                    $actions->addLink(
                        $controller->url_for('shared/contacts/add_ranges_to_contact', $mvv_contact->contact_id),
                        _('Ansprechpartner zuordnen'),
                        Icon::create('person+add'),
                        ['data-dialog' => 'size=auto']
                    );
                    $actions->addLink(
                        $controller->url_for('shared/contacts/delete_all_ranges', $mvv_contact->contact_id),
                        _('Alle Zuordnungen löschen'),
                        Icon::create('trash'),
                        [
                            'data-confirm' => _('Wollen Sie wirklich alle Zuordnungen entfernen?'),
                            'data-dialog' => 'size=auto'
                        ]
                    );
                }
                if ($mvv_contact->contact_status === 'extern' && $perm->havePerm(MvvPerm::PERM_CREATE)) {
                    $actions->addLink(
                        $controller->url_for('shared/contacts/delete_extern_contact', $mvv_contact->contact_id),
                        _('Externe Person löschen'),
                        Icon::create('trash'),
                        [
                            'data-confirm' => _('Wollen Sie die externe Person wirklich löschen?'),
                            'data-dialog' => 'size=auto'
                        ]
                    );
                }
                echo $actions;
            ?>
            </td>
        </tr>
        <? if ($contact_id == $mvv_contact->contact_id) : ?>
            <tr class="loaded-details nohover">
                <?= $this->render_partial('shared/contacts/details', compact('mvv_contact')) ?>
            </tr>
        <? endif; ?>
    </tbody>
    <? endforeach; ?>
    <? if ($count > MVVController::$items_per_page) : ?>
        <tfoot>
            <tr>
                <td colspan="10" style="text-align: right">
                    <?
                    $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                    $pagination->clear_attributes();
                    $pagination->set_attribute('perPage', MVVController::$items_per_page);
                    $pagination->set_attribute('num_postings', $count);
                    $pagination->set_attribute('page', $page);
                    $page_link = reset(explode('?', $controller->url_for('/index'))) . '?page_contacts=%s';
                    $pagination->set_attribute('pagelink', $page_link);
                    echo $pagination->render('shared/pagechooser');
                    ?>
                </td>
            </tr>
        </tfoot>
    <? endif; ?>
<? endif; ?>
</table>
<script type="text/javascript">
jQuery(function ($) {
    $(document).on('dialog-close', function(event) {
        if ($('div.ui-dialog.studip-confirmation').length) {
            STUDIP.MVV.Contact.reload_contacttable();
        }
    });
});
</script>
