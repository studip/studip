<nav>
    <? if ($previous_page) : ?>
        <a href="<?= $controller->link_for('files/all_files', ['page' => $previous_page]) ?>">
            <?= Icon::create('arr_2left') ?>
        </a>
    <? endif ?>
    <? if ($next_page) : ?>
        <a href="<?= $controller->link_for('files/all_files', ['page' => $next_page]) ?>">
            <?= Icon::create('arr_2right') ?>
        </a>
    <? endif ?>
</nav>
<table class="default documents sortable-table" data-sortlist="[[5, 1]]" data-shiftcheck>
    <? if ($previous_page || $next_page) : ?>
        <caption><?= htmlReady(sprintf(_('Alle Dateien, Seite %d'), $page)) ?></caption>
    <? else : ?>
        <caption><?= _('Alle Dateien') ?></caption>
    <? endif ?>
    <?= $this->render_partial(
        'files/_files_thead.php',
        [
            'show_downloads' => $show_download_column,
            'show_bulk_checkboxes' => true
        ]
    ) ?>
    <?= $this->render_partial(
        'files/_flat_tfoot',
        [
            'show_downloads' => $show_downloads,
            'writable'       => true,
            'pagination'     => [$page, $file_ref_c, $page_size]
        ]
    ) ?>
    <tbody class="files">
        <? foreach ($new_files as $file_ref) : ?>
            <?
            $folder = $file_ref->folder;
            if ($folder instanceof Folder) {
                $folder = $folder->getTypedFolder();
            }
            ?>
            <?= $this->render_partial('files/_fileref_tr', [
                'file_ref'       => $file_ref,
                'current_folder' => $folder,
                'controllerpath' => $controllerpath,
                'show_downloads' => $show_download_column,
                'show_bulk_checkboxes' => true
            ]) ?>
        <? endforeach ?>
    </tbody>
</table>
