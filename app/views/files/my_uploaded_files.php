<table class="default documents sortable-table" data-sortlist="[[1, 0]]" data-shiftcheck>
    <? if ($undefined_license_view) : ?>
        <caption><?= _('Meine hochgeladenen Dateien mit ungeklärter Lizenz') ?></caption>
    <? else : ?>
        <caption><?= _('Meine hochgeladenen Dateien') ?></caption>
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
            'writable'       => true
        ]
    ) ?>
    <tbody class="files">
        <? foreach ($file_refs as $file_ref) : ?>
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
