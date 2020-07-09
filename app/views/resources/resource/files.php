<? if ($folder): ?>
    <form method="post" action="<?= $controller->link_for('file/bulk/' . $folder->getId()) ?>">
        <?= CSRFProtection::tokenTag() ?>
        <input type="hidden" name="parent_folder_id" value="<?= $folder->getId() ?>">
        <table class="default documents sortable-table" data-sortlist="[[2, 0]]" data-folder_id="<?= $folder->getId() ?>">
            <caption>
                <a href="<?= $controller->url_for('resources/resource/files/' . $resource->id)?>"
                   title="<?= _('Zum Hauptordner') ?>">
                    <?= Icon::create('folder-home-full', 'clickable')->asImg(30, ['class' => 'text-bottom']) ?>
                </a>
                <?= htmlReady($resource->getFullName()) ?></caption>
            <?= $this->render_partial('files/_files_thead') ?>
            <tbody>
                <? if (count($folder_files)): ?>
                    <? foreach($folder_files as $file): ?>
                        <? if ($file->isVisible($GLOBALS['user']->id)) : ?>
                            <?= $this->render_partial(
                                'files/_fileref_tr',
                                [
                                    'file'           => $file,
                                    'current_folder' => $folder,
                                    'controllerpath' => 'resources/resource/files',
                                    'last_visitdate' => $last_visitdate
                                ]
                            ) ?>
                        <? endif ?>
                    <? endforeach ?>
                <? else: ?>
                    <tr class="empty">
                        <td colspan="7">
                            <?= _('Dieser Ordner ist leer') ?>
                        </td>
                    </tr>
                <? endif ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7">
                        <span class="multibuttons">
                            <?= Studip\Button::create(
                                _('Herunterladen'),
                                'download'
                            ) ?>
                            <? if ($folder->isWritable($GLOBALS['user']->id)): ?>
                                <?= Studip\Button::create(
                                    _('Verschieben'),
                                    'move',
                                    [
                                        'data-dialog' => '',
                                        'disabled' => 'disabled'
                                    ]
                                ) ?>
                            <? endif ?>
                            <? if ($folder->isReadable($GLOBALS['user']->id)): ?>
                                <?= Studip\Button::create(
                                    _('Kopieren'),
                                    'copy',
                                    [
                                        'data-dialog' => '',
                                        'disabled'    => '',
                                    ]
                                ) ?>
                            <? endif ?>
                            <? if ($folder->isWritable($GLOBALS['user']->id)): ?>
                                <?= Studip\Button::create(
                                    _('Löschen'),
                                    'delete', [
                                        'disabled'     => '',
                                        'data-confirm' => _('Soll die Auswahl wirklich gelöscht werden?')
                                    ]
                                ) ?>
                            <? endif ?>
                            <? if ($folder->isWritable($GLOBALS['user']->id)): ?>
                                <?= Studip\LinkButton::create(
                                    _('Dokument hinzufügen'),
                                    '#',
                                    [
                                        'onclick' => 'STUDIP.Files.openAddFilesWindow(); return false;'
                                    ]
                                ) ?>
                            <? endif ?>
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
    <? if ($GLOBALS['user']->id !== 'nobody'): ?>
        <?= $this->render_partial('file/upload_window.php') ?>
        <?= $this->render_partial(
            'file/add_files_window.php',
            [
                'folder_id' => $folder->getId(),
                'hidden'    => true,
                'upload_type' => FileManager::getUploadTypeConfig(
                    $folder->range_id,
                    $GLOBALS['user']->id
                )
            ]
        ) ?>
    <? endif ?>
<? endif ?>
