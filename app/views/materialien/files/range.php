<div id="messagebox-container">
<? if (Request::isXhr()) : ?>
    <? foreach (PageLayout::getMessages() as $messagebox) : ?>
        <?= $messagebox ?>
    <? endforeach ?>
<? endif; ?>
</div>
<table id="mvv_files" class="default sortable-table" data-sortlist="[[0, 0]]">
    <caption>
        <span class="actions">
            <a href="<?= $controller->url_for('materialien/files/add_dokument', 'range', $range_type, $range_id);?>" data-dialog="">
                <?= Icon::create('upload+add', Icon::ROLE_CLICKABLE, ['title' => _('neues Dokument hinzufügen')]); ?>
            </a>
            <a href="<?= $controller->url_for('materialien/files/add_files_to_range',$range_type, $range_id);?>" data-dialog="">
                <?= Icon::create('file+add', Icon::ROLE_CLICKABLE, ['title' => _('vorhandenes Dokument hinzufügen')]); ?>
            </a>
            <a href="<?= $controller->url_for('materialien/files/sort', $range_type, $range_id);?>" data-dialog="size=auto">
                <?= Icon::create('arr_2up', Icon::ROLE_CLICKABLE, ['title' => _('Reihenfolge der Dokumente ändern')]); ?>
            </a>
        </span>
    </caption>
    <thead>
        <tr class="sortable">
            <th data-sorter="digit"><?= _('Pos.') ?></th>
            <th data-sorter="text"><?= _('Name') ?></th>
            <th data-sorter="text"><?= _('Dateiname') ?></th>
            <th data-sorter="digit"><?= _('Sichtbarkeit') ?></th>
            <th><?= _('Sprache') ?></th>
            <th data-sorter="text"><?= _('Art der Datei') ?></th>
            <th data-sorter="digit"><?= _('Datum') ?></th>
            <th data-sorter="text"><?= _('Dateityp') ?></th>
            <th data-sorter="text"><?= _('Kategorie') ?></th>
            <th data-sorter="digit"><?= _('Zuordnungen') ?></th>
            <th data-sorter="false" style="text-align: right;"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
<? if($dokumente): ?>
    <tbody>
    <? foreach($dokumente as $mvv_file): ?>
        <tr>
            <td><?= htmlReady($mvv_file->getPositionInRange($range_id)); ?></td>
            <td><?= htmlReady($mvv_file->getDisplayName()) ?></td>
            <td data-sort-value="<?= htmlReady($mvv_file->getFilenames()[0]); ?>">
                <? if($mvv_file->getFiletypes()[0] == 'Link'): ?>
                    <a href="<?= htmlReady($mvv_file->getFilenames()[0]); ?>" target="_blank">
                        <?= Icon::create('link-extern', Icon::ROLE_CLICKABLE, ['class' => 'text-bottom']); ?>
                        <?= htmlReady($mvv_file->getFilenames()[0]); ?>
                    </a>
                <? else: ?>
                    <?= htmlReady($mvv_file->getFilenames()[0]); ?>
                <? endif; ?>
            </td>
            <td style="text-align: center;" data-sort-value="<?= $mvv_file->extern_visible?'1':'0' ?>">
                <?= Icon::create(
                        $mvv_file->extern_visible?'visibility-visible':'visibility-invisible',
                        Icon::ROLE_INFO,
                        [
                            'class' => 'text-bottom',
                            'title' => $mvv_file->extern_visible?_('sichtbar'):_('unsichtbar')
                        ]
                    );
                ?>
            </td>
            <td>
                <? foreach ($mvv_file->file_refs as $fileref) : ?>
                    <?= Assets::img('languages/lang_' . mb_strtolower($fileref->file_language) . '.gif') ?>
                <? endforeach; ?>
            </td>
            <td><?= htmlReady($GLOBALS['MVV_DOCUMENTS']['TYPE']['values'][$mvv_file->type]['name']); ?></td>
            <td data-sort-value="<?= htmlReady($mvv_file->mkdate); ?>"><?= htmlReady(date('d.m.Y', $mvv_file->mkdate)); ?></td>
            <td style="text-align: center;"><?= htmlReady($mvv_file->getFiletypes()[0]); ?></td>
            <td><?= htmlReady($GLOBALS['MVV_DOCUMENTS']['CATEGORY']['values'][$mvv_file->category]['name']); ?></td>
            <td style="text-align: center;"><?= htmlReady($mvv_file->count_relations); ?></td>
            <td class="actions">
            <?
                $actions = ActionMenu::get();
                $actions->addLink(
                    $controller->url_for('materialien/files/details',$mvv_file->mvvfile_id),
                    _('Details'),
                    Icon::create('info-circle'),
                    ['data-dialog' => 'size=auto']
                );
                $actions->addLink(
                    $controller->url_for('materialien/files/add_dokument','range', $range_type, $range_id, $mvv_file->mvvfile_id),
                    _('Dokument bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => '']
                );
                foreach ($mvv_file->file_refs as $fileref) {
                    $actions->addLink(
                        $fileref->file_ref->getDownloadURL('force_download'),
                        _('Datei herunterladen') . ' (' . $fileref->file_language . ')',
                        Icon::create('download', 'clickable', ['size' => 20]),
                        ['target' => '_blank']
                    );
                }
                $actions->addLink(
                    $controller->url_for('materialien/files/delete_range', $mvv_file->mvvfile_id, $range_id),
                    _('Dokument-Zuordnung löschen'),
                    Icon::create('trash'),
                    [
                        'data-confirm' => _('Wollen Sie die Zuordnung des Dokuments wirklich entfernen?'),
                        'data-dialog' => 'size=auto'
                    ]
                );
                echo $actions;
            ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
<? endif; ?>
</table>
