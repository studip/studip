<?php
$options = [];
if (Request::get('to_plugin')) {
    $options['to_plugin'] = Request::get('to_plugin');
}
if (Request::get('from_plugin')) {
    $options['from_plugin'] = Request::get('from_plugin');
}
if (Request::get('to_folder_id')) {
    $options['to_folder_id'] = Request::get('to_folder_id');
}
if ($folder_id) {
    $options['to_folder_id'] = $folder_id;
}

?>
<div class="files_source_selector" data-folder_id="<?= htmlReady($folder_id) ?>" <? if ($hidden) echo ' style="display: none;"'; ?>>
    <?= _('Quelle auswählen') ?>
    <div class="file_select_possibilities">
        <a href="#" onclick="jQuery('.file_selector input[type=file]').first().click(); return false;">
            <?= Icon::create('computer', Icon::ROLE_CLICKABLE)->asImg(50) ?>
            <?= _('Mein Computer') ?>
        </a>
        <a href="<?= $controller->link_for('file/add_url/' . $folder_id, array_merge($options, ['from_plugin' => ""])) ?>" data-dialog>
            <?= Icon::create('globe', Icon::ROLE_CLICKABLE)->asImg(50) ?>
            <?= _('Webadresse') ?>
        </a>
        <a href="<?= $controller->link_for('file/choose_file/' . Folder::findTopFolder($GLOBALS['user']->id)->getId(), array_merge($options, ['from_plugin' => ""])) ?>" data-dialog>
            <?= Icon::create('files', Icon::ROLE_CLICKABLE)->asImg(50) ?>
            <?= _('Meine Dateien') ?>
        </a>
        <a href="<?= $controller->link_for('file/choose_file_from_course/' . htmlReady($folder_id), array_merge($options, ['from_plugin' => ""])) ?>" data-dialog>
            <?= Icon::create('seminar', Icon::ROLE_CLICKABLE)->asImg(50) ?>
            <?= _('Meine Veranstaltungen') ?>
        </a>
    <? foreach (PluginManager::getInstance()->getPlugins('FilesystemPlugin') as $plugin) : ?>
        <? if ($plugin->isSource()) : ?>
            <? $nav = $plugin->getFileSelectNavigation() ?>
            <? if ($nav): ?>
                <a href="<?= $controller->link_for('file/choose_file/', array_merge($options, ['from_plugin' => get_class($plugin)])) ?>" data-dialog>
                    <?= $nav->getImage()->asImg(50) ?>
                    <?= htmlReady($nav->getTitle()) ?>
                </a>
            <? endif; ?>
        <? endif; ?>
    <? endforeach; ?>
    </div>
    <div>
        <?=sprintf(_('Sie dürfen Dateien bis zu einer Größe von %s in diesem Bereich einstellen.'), '<b>' . relsize($upload_type['file_size']) . '</b>')?>
    </div>
    <? if (count($upload_type['file_types']) && $upload_type['type'] == 'allow') : ?>
        <div>
            <?=sprintf(_('Sie dürfen die Dateitypen %s nicht hochladen!'), '<b>' . join($upload_type['file_types'],',') . '</b>')?>
        </div>
    <? endif ?>
    <? if (count($upload_type['file_types']) && $upload_type['type'] == 'deny') : ?>
        <div>
            <?=sprintf(_('Sie dürfen nur die Dateitypen %s hochladen!'), '<b>' . join($upload_type['file_types'],',') . '</b>')?>
        </div>
    <? endif ?>
    <form style="display: none;" class="file_selector">

        <input type="file" name="files[]" multiple onchange="STUDIP.Files.upload(this.files);">
    </form>
</div>

<div style="display: none;">
    <?= _('Soll die hochgeladene ZIP-Datei entpackt werden?') ?>
</div>
