<?php

$actionMenu = ActionMenu::get();

$actionMenu->addLink(
    $fileRef->getDownloadURL('force_download'),
    _('Download'),
    Icon::create('download', Icon::ROLE_CLICKABLE, ['size' => 20])
);

$actionMenu->addLink(
    $controller->url_for('file/details/'.$fileRef->id),
    _('Info'),
    Icon::create('info-circle', Icon::ROLE_CLICKABLE, ['size' => 20]),
    ['data-dialog' => 1]
);

if (Navigation::hasItem('/course/files_new/flat')
    && Navigation::getItem('/course/files_new/flat')->isActive()) {
    $actionMenu->addLink(
        $controller->url_for('course/files/index/'.$fileRef->folder_id),
        _('Ordner öffnen'),
        Icon::create('folder-empty', Icon::ROLE_CLICKABLE, ['size' => 20])
    );
} elseif (Navigation::hasItem('/files_dashboard/files/flat')
          && Navigation::getItem('/files_dashboard/files/flat')->isActive()) {
    $actionMenu->addLink(
        $controller->url_for('files/index/'.$fileRef->folder_id),
        _('Ordner öffnen'),
        Icon::create('folder-empty', Icon::ROLE_CLICKABLE, ['size' => 20])
    );
}

if ($currentFolder->isFileEditable($fileRef->id, $userId)) {
    $actionMenu->addLink(
        $controller->url_for('file/edit/'.$fileRef->id),
        _('Datei bearbeiten'),
        Icon::create('edit', Icon::ROLE_CLICKABLE, ['size' => 20]),
        ['data-dialog' => '']
    );
    $actionMenu->addLink(
        $controller->url_for('file/update/'.$fileRef->id),
        _('Datei aktualisieren'),
        Icon::create('refresh', Icon::ROLE_CLICKABLE, ['size' => 20]),
        ['data-dialog' => '']
    );
}

if ($currentFolder->isFileWritable($fileRef->id, $userId)) {
    $actionMenu->addLink(
        $controller->url_for('file/choose_destination/move/'.$fileRef->id),
        _('Datei verschieben'),
        Icon::create('file+move_right', Icon::ROLE_CLICKABLE, ['size' => 20]),
        ['data-dialog' => 'size=auto']
    );
}

if ($currentFolder->isFileDownloadable($fileRef, $userId)) {
    $actionMenu->addLink(
        $controller->url_for('file/choose_destination/copy/'.$fileRef->id),
        _('Datei kopieren'),
        Icon::create('file+add', Icon::ROLE_CLICKABLE, ['size' => 20]),
        ['data-dialog' => 'size=auto']
    );
}

if ($currentFolder->isFileWritable($fileRef->id, $userId)) {
    $actionMenu->addLink(
        $controller->url_for('file/delete/'.$fileRef->id),
        _('Datei löschen'),
        Icon::create('trash', Icon::ROLE_CLICKABLE, ['size' => 20]),
        ['onclick' => "return STUDIP.Dialog.confirmAsPost('".sprintf(_('Soll die Datei "%s" wirklich gelöscht werden?'), htmlReady($fileRef->name))."', this.href);"]
    );
}

echo $actionMenu->render();
