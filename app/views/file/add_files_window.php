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
    <h2 class="dialog-subtitle"><?= _('Quelle auswählen') ?></h2>
    <div class="file_select_possibilities">
        <? if (($range instanceof Course) && !$range->getSemClass()['studygroup_mode'] && $GLOBALS['perm']->have_studip_perm('tutor', $range->id) && $GLOBALS['LIBRARY_CATALOGS'] && $show_library_functions) : ?>
            <div>
                <a class="important-item" data-dialog="size=medium-43"
                   href="<?= $controller->link_for('file/add_from_library/' . $folder_id)?>">
                    <div class="icon">
                        <?= Icon::create('literature')->asImg(50) ?>
                        <div><?= _('Bibliothek') ?></div>
                    </div>
                    <div class="description">
                        <strong><?= _('Originaldokument aus Bibliotheksverzeichnissen einbinden') ?></strong>
                        <div><?= htmlReady($library_search_description) ?></div>
                    </div>
                </a>
            </div>
        <? endif ?>
        <div>
            <a href="#" onclick="jQuery('.file_selector input[type=file]').first().click(); return false;">
                <?= Icon::create('computer')->asImg(50) ?>
                <?= _('Mein Computer') ?>
            </a>
            <a href="<?= $controller->link_for('file/add_url/' . $folder_id, array_merge($options, ['from_plugin' => ""])) ?>" data-dialog>
                <?= Icon::create('globe')->asImg(50) ?>
                <?= _('Webadresse') ?>
            </a>
            <a href="<?= $controller->link_for('file/choose_file/' . Folder::findTopFolder($GLOBALS['user']->id)->getId(), array_merge($options, ['from_plugin' => ""])) ?>" data-dialog>
                <?= Icon::create('files')->asImg(50) ?>
                <?= _('Persönlicher Dateibereich') ?>
            </a>
            <a href="<?= $controller->link_for('file/choose_file_from_course/' . htmlReady($folder_id), array_merge($options, ['from_plugin' => ""])) ?>" data-dialog>
                <?= Icon::create('seminar')->asImg(50) ?>
                <?= _('Meine Veranstaltungen') ?>
            </a>
            <? if (($range instanceof Course) && $GLOBALS['perm']->have_studip_perm('tutor', $range->id) && $show_library_functions) : ?>
                <a href="<?= $controller->link_for('library_file/select_type/' . htmlReady($folder_id)) ?>"
                   data-dialog="size=auto">
                    <?= Icon::create('literature')->asImg(50) ?>
                    <?= _('Literatur') ?>
                </a>
            <? endif ?>
            <? if (Config::get()->OERCAMPUS_ENABLED && $GLOBALS['perm']->have_perm(Config::get()->OERCAMPUS_PUBLIC_STATUS)) : ?>
                <a href="<?= $controller->link_for('oer/addfile/choose_file', array_merge($options, ['from_plugin' => ""])) ?>"
                   data-dialog="height=800">
                    <?= Icon::create('service', Icon::ROLE_CLICKABLE)->asImg(50) ?>
                    <?= htmlReady(Config::get()->OER_TITLE) ?>
                </a>
            <? endif ?>
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
    </div>
    <div>
        <?=sprintf(_('Sie dürfen Dateien bis zu einer Größe von %s in diesem Bereich einstellen.'), '<b>' . relsize($upload_type['file_size']) . '</b>')?>
    </div>
    <? if (count($upload_type['file_types']) && $upload_type['type'] == 'allow') : ?>
        <div>
            <?=sprintf(_('Sie dürfen die Dateitypen %s nicht hochladen!'), '<b>' . join(',', $upload_type['file_types']) . '</b>')?>
        </div>
    <? endif ?>
    <? if (count($upload_type['file_types']) && $upload_type['type'] == 'deny') : ?>
        <div>
            <?=sprintf(_('Sie dürfen nur die Dateitypen %s hochladen!'), '<b>' . join(',', $upload_type['file_types']) . '</b>')?>
        </div>
    <? endif ?>
    <form style="display: none;" class="file_selector">

        <input type="file" name="files[]" multiple onchange="STUDIP.Files.upload(this.files);">
    </form>
</div>

<div style="display: none;">
    <?= _('Soll die hochgeladene ZIP-Datei entpackt werden?') ?>
</div>
