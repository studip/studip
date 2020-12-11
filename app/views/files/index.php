<? if ($topFolder): ?>
    <?php
    if (!$controllerpath) {
        $controllerpath = 'files/index';
        if ($topFolder->range_type !== 'user') {
            $controllerpath = $topFolder->range_type . '/' . $controllerpath;
        }
    }

    $show_downloads = Config::get()->DISPLAY_DOWNLOAD_COUNTER === 'always';
    $vue_breadcrumbs = [];
    $folder = $topFolder;
    do {
        $vue_breadcrumbs[] = [
            'folder_id' => $folder->getId(),
            'name' => $folder->name,
            'url' => $controller->url_for($controllerpath . '/' . $folder->getId())
        ];
    } while ($folder = $folder->getParent());

    $vue_topFolder = [
        'description' => $topFolder->getDescriptionTemplate(),
        'additionalColumns' => $topFolder->getAdditionalColumns(),
        'buttons' => null
    ];
    if (is_a($vue_topFolder['description'], "Flexi_Template")) {
        $vue_topFolder['description'] = $vue_topFolder['description']->render();
    }
    $vue_files = [];
    foreach ($topFolder->getFiles() as $file) {
        if ($file->isVisible($GLOBALS['user']->id)) {
            $vue_files[] = FilesystemVueDataManager::getFileVueData($file, $topFolder, $last_visitdate);
        }
    }
    $vue_folders = [];
    foreach ($topFolder->getSubfolders() as $folder) {
        if ($folder->isVisible($GLOBALS['user']->id)) {
            $vue_folders[] = FilesystemVueDataManager::getFolderVueData($folder, $topFolder, $last_visitdate);
        }
    }

    $vue_topFolder['buttons'] = '<span class="multibuttons">';
    $vue_topFolder['buttons'] .= Studip\Button::create(_('Herunterladen'), 'download', [
        'data-activates-condition' => 'table.documents tr[data-permissions*=d] :checkbox:checked'
    ]);
    if ($topFolder->isWritable($GLOBALS['user']->id)) {
        $vue_topFolder['buttons'] .= Studip\Button::create(_('Verschieben'), 'move', [
            'formaction'  => $controller->url_for('file/choose_destination/move/bulk'),
            'data-dialog' => 'size=auto',
            'data-activates-condition' => 'table.documents tr[data-permissions*=w] :checkbox:checked'
        ]);
    }
    if ($topFolder->isReadable($GLOBALS['user']->id)) {
        $vue_topFolder['buttons'] .= Studip\Button::create(_('Kopieren'), 'copy', [
            'formaction'  => $controller->url_for('file/choose_destination/copy/bulk'),
            'data-dialog' => 'size=auto',
            'data-activates-condition' => 'table.documents tr[data-permissions*=r] :checkbox:checked'
        ]);
    }
    if ($topFolder->isWritable($GLOBALS['user']->id)) {
        $vue_topFolder['buttons'] .= Studip\Button::create(_('Löschen'), 'delete', [
            'data-confirm'             => _('Soll die Auswahl wirklich gelöscht werden?'),
            'data-activates-condition' => 'table.documents tr[data-permissions*=w] :checkbox:checked'
        ]);
    }
    $vue_topFolder['buttons'] .= '</span>';
    if ($topFolder->isSubfolderAllowed($GLOBALS['user']->id)) {
        $vue_topFolder['buttons'] .= Studip\LinkButton::create(
            _('Neuer Ordner'),
            $controller->url_for('file/new_folder/' . $topFolder->getId()),
            ['data-dialog' => '']
        );
    }
    if ($topFolder->isWritable($GLOBALS['user']->id)) {
        $vue_topFolder['buttons'] .= Studip\LinkButton::create(_('Dokument hinzufügen'), '#', [
            'onclick' => 'STUDIP.Files.openAddFilesWindow(); return false;'
        ]);
    }
    foreach ($topFolder->getAdditionalActionButtons() as $button) {
        $vue_topFolder['buttons'] .= $button;
    }
    ?>

    <? if ($show_file_search) : ?>
        <form class="default" method="get" action="<?= $controller->link_for('files_dashboard/search') ?>">
            <?= $this->render_partial('files_dashboard/_input-group-search') ?>
        </form>
    <? endif ?>

    <form method="post"
          id="files_table_form"
          action="<?= $controller->link_for('file/bulk/' . $topFolder->getId()) ?>"
          data-files="<?= htmlReady(json_encode($vue_files)) ?>"
          data-folders="<?= htmlReady(json_encode((array) $vue_folders)) ?>"
          data-breadcrumbs="<?= htmlReady(json_encode((array) array_reverse($vue_breadcrumbs))) ?>"
          data-topfolder="<?= htmlReady(json_encode((array) $vue_topFolder)) ?>">
        <?= CSRFProtection::tokenTag() ?>
        <input type="hidden" name="parent_folder_id" value="<?= $topFolder->getId() ?>">
        <files-table :showdownloads="<?= $show_downloads ? "true" : "false" ?>"
                     :breadcrumbs="breadcrumbs"
                     :files="files"
                     :folders="folders"
                     :topfolder="topfolder"
        ></files-table>
    </form>
    <? if ($GLOBALS['user']->id !== 'nobody') : ?>

        <?= $this->render_partial('file/upload_window.php') ?>
        <?= $this->render_partial('file/add_files_window.php', [
            'folder_id' => $topFolder->getId(),
            'hidden'    => true,
            'range'   => $topFolder instanceof StandardFolder ? $topFolder->getRangeObject() : null,
            'upload_type' => FileManager::getUploadTypeConfig($topFolder->range_id, $GLOBALS['user']->id),
            'show_library_functions' => Config::get()->LITERATURE_ENABLE,
            'library_search_description' => Config::get()->LIBRARY_ADD_ITEM_ACTION_DESCRIPTION
        ]) ?>
    <? endif ?>
<? endif ?>
<?= Feedback::getHTML($topFolder->getId(), 'Folder') ?>
