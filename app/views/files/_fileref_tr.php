<?php
$permissions = [];
if ($current_folder->isFileEditable($file_ref->id, $GLOBALS['user']->id)) {
    $permissions[] = 'w';
}
if ($current_folder->isFileDownloadable($file_ref->id, $GLOBALS['user']->id)) {
    $permissions[] = 'dr';
}
?>
<tr class="<? if ($file_ref->chdate > $last_visitdate && ($file_ref->user_id !== $GLOBALS['user']->id)) echo 'new'; ?>"
    <? if ($full_access) printf('data-file="%s"', $file_ref->id) ?>
    id="fileref_<?= htmlReady($file_ref->id) ?>"
    role="row"
    data-permissions="<?= implode($permissions) ?>">
    <td>
    <? if ($current_folder->isFileDownloadable($file_ref, $GLOBALS['user']->id)) : ?>
        <input type="checkbox"
               class="studip-checkbox"
               name="ids[]"
               id="file_checkbox_<?= $file_ref->id ?>"
               value="<?= $file_ref->id ?>"
               <? if (in_array($file_ref->id, (array)$marked_element_ids)) echo 'checked'; ?>>
        <label for="file_checkbox_<?= $file_ref->id ?>"></label>
    <? endif ?>
    </td>
    <td class="document-icon" data-sort-value="<?=crc32($file_ref->mime_type)?>">
    <? if ($current_folder->isFileDownloadable($file_ref, $GLOBALS['user']->id)) : ?>
        <a href="<?= htmlReady($file_ref->download_url) ?>" target="_blank" rel="noopener noreferrer">
            <?= FileManager::getIconForFileRef($file_ref)->asImg(24) ?>
        </a>
    <? else : ?>
        <?= FileManager::getIconForFileRef($file_ref, Icon::ROLE_INACTIVE)->asImg(24) ?>
    <? endif ?>
    </td>
    <td data-sort-value="<?= htmlReady($file_ref->name) ?>">
    <? if ($current_folder->isFileDownloadable($file_ref, $GLOBALS['user']->id)) : ?>
        <a href="<?= $controller->link_for("file/details/{$file_ref->id}/1") ?>" data-dialog>
            <?= htmlReady($file_ref->name) ?>
        </a>
    <? else : ?>
        <?= htmlReady($file_ref->name) ?>
    <? endif ?>
    <? if ($file_ref->terms_of_use && !$file_ref->terms_of_use->fileIsDownloadable($file_ref, false)): ?>
        <?= Icon::create('lock-locked', Icon::ROLE_INFO)->asImg(['title' => _('Das Herunterladen dieser Datei ist nur eingeschränkt möglich.')]) ?>
    <? endif; ?>
    </td>
    <td title="<?= number_format($file_ref->size, 0, ',', '.') . ' Byte' ?>" data-sort-value="<?= $file_ref->size ?>" class="responsive-hidden">
    <? if ($file_ref->is_link) : ?>
        <?= _('Weblink') ?>
    <? else : ?>
        <?= relSize($file_ref->size, false) ?>
    <? endif ?>
    </td>
<? if ($show_downloads) : ?>
    <td data-sort-value="<?= htmlReady($file_ref->downloads) ?>" class="responsive-hidden">
        <?= htmlReady($file_ref->downloads) ?>
    </td>
<? endif ?>
    <td data-sort-value="<?= htmlReady($file_ref->author_name) ?>" class="responsive-hidden">
    <? if ($file_ref->user_id !== $GLOBALS['user']->id && $file_ref->owner): ?>
        <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $file_ref->owner->username) ?>">
            <?= htmlReady($file_ref->author_name) ?>
        </a>
    <? else: ?>
        <?= htmlReady($file_ref->author_name) ?>
    <? endif; ?>
    </td>
    <td title="<?= strftime('%x %X', $file_ref->chdate) ?>" data-sort-value="<?= $file_ref->chdate ?>" class="responsive-hidden">
        <?= $file_ref->chdate ? reltime($file_ref->chdate) : "" ?>
    </td>
    <td class="actions">
    <?php
        $actionMenu = ActionMenu::get();
        $actionMenu->addLink(
            $controller->url_for("file/details/{$file_ref->id}/1"),
            _('Info'),
            Icon::create('info-circle', Icon::ROLE_CLICKABLE, ['size' => 20]),
            ['data-dialog' => '']
        );
        if ($current_action === 'flat') {
            if (Navigation::hasItem('/course/files') && Navigation::getItem('/course/files')->isActive()) {
                $actionMenu->addLink(
                    $controller->url_for('course/files/index/' . $file_ref->folder_id),
                    _('Ordner öffnen'),
                    Icon::create('folder-empty', Icon::ROLE_CLICKABLE, ['size' => 20])
                );
            } elseif (Navigation::hasItem('/files_dashboard/files') && Navigation::getItem('/files_dashboard/files')->isActive()) {
                 $actionMenu->addLink(
                     $controller->url_for('files/index/' . $file_ref->folder_id),
                     _('Ordner öffnen'),
                     Icon::create('folder-empty', Icon::ROLE_CLICKABLE, ['size' => 20])
                );
            }
        }
        if ($current_folder->isFileEditable($file_ref->id, $GLOBALS['user']->id)) {
            $actionMenu->addLink(
                $controller->url_for('file/edit/' . $file_ref->id),
                _('Datei bearbeiten'),
                Icon::create('edit', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['data-dialog' => '']
            );
            $actionMenu->addLink(
                $controller->url_for('file/update/' . $file_ref->id),
                _('Datei aktualisieren'),
                Icon::create('refresh', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['data-dialog' => '']
            );
        }
        if ($current_folder->isFileWritable($file_ref->id, $GLOBALS['user']->id)) {
            $actionMenu->addLink(
                $controller->url_for('file/choose_destination/move/' . $file_ref->id),
                _('Datei verschieben'),
                Icon::create('file+move_right', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['data-dialog' => 'size=auto']
            );
        }
        if ($current_folder->isFileDownloadable($file_ref, $GLOBALS['user']->id) && $GLOBALS['user']->id !== 'nobody') {
            $actionMenu->addLink(
                $controller->url_for('file/choose_destination/copy/' . $file_ref->id),
                _('Datei kopieren'),
                Icon::create('file+add', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['data-dialog' => 'size=auto']
            );
        }
        if ($current_folder->isFileWritable($file_ref->id, $GLOBALS['user']->id)) {
            $actionMenu->addLink(
                (
                    $flat_view
                    ? URLHelper::getURL(
                        'dispatch.php/file/delete/' . $file_ref->id,
                        [
                            'from_flat_view' => '1'
                        ]
                    )
                    : $controller->url_for('file/delete/' . $file_ref->id)
                ),
                _('Datei löschen'),
                Icon::create('trash', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['onclick' => "return STUDIP.Dialog.confirmAsPost('" . sprintf(_('Soll die Datei "%s" wirklich gelöscht werden?'), htmlReady($file_ref->name)) . "', this.href);"]
            );
        }
    ?>
        <?= $actionMenu->render() ?>
    </td>
</tr>
