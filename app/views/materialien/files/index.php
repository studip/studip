<table id="mvv_files" class="default collapsable">
    <caption>
        <?= _('Verlinkte Dokumente') ?>
        <span class="actions"><? printf(ngettext('%s Dokument', '%s Dokumente', $count), $count) ?></span>
    </caption>
    <thead>
        <tr class="sortable">
            <?= $controller->renderSortLink('materialien/files/', _('Name'), 'mvv_files_filerefs.name') ?>
            <?= $controller->renderSortLink('materialien/files/', _('Dateiname'), 'file_refs.name') ?>
            <?= $controller->renderSortLInk('materialien/files/', _('Sichtbarkeit'), 'extern_visible') ?>
            <?= $controller->renderSortLInk('materialien/files/', _('Sprache'), 'file_language') ?>
            <?= $controller->renderSortLink('materialien/files/', _('Art der Datei'), 'type') ?>
            <?= $controller->renderSortLink('materialien/files/', _('Datum'), 'mkdate') ?>
            <th><?= _('Dateityp') ?></th>
            <?= $controller->renderSortLInk('materialien/files/', _('Kategorie'), 'category') ?>
            <?= $controller->renderSortLink('materialien/files/', _('Zuordnungen'), 'count_relations') ?>
            <th class="actions" style="width: 5%"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
<? foreach ($dokumente as $mvv_file) : ?>
    <tbody class="<?= (in_array($range_id, $mvv_file->getRangesArray()) && ($fileref_id && in_array($fileref_id, $mvv_file->getFileRefsArray())) ? 'not-collapsed' : 'collapsed') ?>">
        <tr class="header-row">
            <td class="toggle-indicator">
                <a class="mvv-load-in-new-row"
                    href="<?= $controller->link_for('materialien/files/details', $mvv_file->mvvfile_id) ?>">
                    <?= htmlReady($mvv_file->getDisplayName()) ?>
                </a>
            </td>
            <td class="dont-hide">
            <? if ($mvv_file->getFiletypes()[0] === 'Link'): ?>
                <a href="<?= htmlReady($mvv_file->file_refs[0]->file_ref->file->metadata['url']); ?>" target="_blank">
                    <?= Icon::create('link-extern')->asImg(['class' => 'text-bottom']) ?>
                    <?= htmlReady($mvv_file->getFilenames()[0]); ?>
                </a>
            <? else: ?>
                <?= htmlReady($mvv_file->getFilenames()[0]); ?>
            <? endif; ?>
            </td>
            <td class="dont-hide" style="text-align: center;">
                <?= Icon::create(
                    $mvv_file->extern_visible?'visibility-visible':'visibility-invisible',
                    Icon::ROLE_INFO
                )->asImg([
                    'class' => 'text-bottom',
                    'title' => $mvv_file->extern_visible?_('sichtbar'):_('unsichtbar')
                ]) ?>
            </td>
            <td class="dont-hide">
            <? foreach ($mvv_file->file_refs as $fileref) : ?>
                <?= Assets::img('languages/lang_' . mb_strtolower($fileref->file_language) . '.gif') ?>
            <? endforeach; ?>
            </td>
            <td class="dont-hide"><?= htmlReady($GLOBALS['MVV_DOCUMENTS']['TYPE']['values'][$mvv_file->type]['name']) ?></td>
            <td class="dont-hide"><?= strftime('%x', $mvv_file->mkdate) ?></td>
            <td class="dont-hide"><?= htmlReady($mvv_file->getFiletypes()[0]) ?></td>
            <td class="dont-hide"><?= htmlReady($GLOBALS['MVV_DOCUMENTS']['CATEGORY']['values'][$mvv_file->category]['name']) ?></td>
            <td class="dont-hide" style="text-align: center;"><?= htmlReady($mvv_file->count_relations) ?></td>
            <td class="dont-hide actions">
            <?
                $actions = ActionMenu::get();
                $actions->addLink(
                    $controller->url_for('materialien/files/add_dokument', 'index', $mvv_file->getRangeType()?:0, 0, $mvv_file->mvvfile_id),
                    _('Dokument bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                );
                $actions->addLink(
                    $controller->url_for('materialien/files/add_ranges_to_file',$mvv_file->mvvfile_id),
                    _('Dokument zuordnen'),
                    Icon::create('file+add'),
                    ['data-dialog' => 'size=auto']
                );
                foreach ($mvv_file->file_refs as $fileref) {
                    $actions->addLink(
                        $fileref->file_ref->getDownloadURL('force_download'),
                        _('Datei herunterladen') . ' (' . $fileref->file_language . ')',
                        Icon::create('download'),
                        ['target' => '_blank']
                    );
                }
                $actions->addLink(
                    $controller->url_for("materialien/files/delete_all_dokument/{$mvv_file->mvvfile_id}"),
                    _('Dokument löschen'),
                    Icon::create('trash'),
                    [
                        'data-confirm' => _('Wollen Sie das Dokument wirklich entfernen?'),
                        'data-dialog' => 'size=auto'
                    ]
                );
                echo $actions->render();
            ?>
            </td>
        </tr>
    <? if (in_array($range_id, $mvv_file->getRangesArray()) && ($fileref_id && in_array($fileref_id, $mvv_file->getFileRefsArray()))) : ?>
        <tr class="loaded-details nohover">
            <?= $this->render_partial('materialien/files/details', compact('mvv_file')) ?>
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
                // ARGH!
                $page_link = reset(explode('?', $controller->url_for('/index'))) . '?page_files=%s';
                $pagination->set_attribute('pagelink', $page_link);
                echo $pagination->render("shared/pagechooser");
                ?>
            </td>
        </tr>
    </tfoot>
<? endif; ?>
</table>
<script type="text/javascript">
jQuery(function ($) {
    $(document).on('dialog-close', function(event) {
        if ($('div.ui-dialog.studip-confirmation').length) {
            STUDIP.MVV.Document.reload_documenttable();
        }
    });
});
</script>
