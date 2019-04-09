<?php
$options = [];
/*if (Request::get('to_plugin')) {
    $options['to_plugin'] = Request::get('to_plugin');
}*/
if (Request::get('from_plugin')) {
    $options['from_plugin'] = Request::get('from_plugin');
}
if (Request::get('to_folder_id')) {
    $options['to_folder_id'] = Request::get('to_folder_id');
}
if (Request::get('copymode') || $copymode) {
    $options['copymode'] = Request::get('copymode', $copymode);
}
if (Request::get('isfolder')) {
    $options['isfolder'] = Request::get('isfolder');
}
if ($fileref_id) {
    $options['fileref_id'] = $fileref_id;
} elseif (Request::getArray('fileref_id')) {
    $options['fileref_id'] = Request::getArray('fileref_id');
}
$options['direct_parent'] = true;
?>

<div class="files_source_selector" <? if ($hidden) echo ' style="display: none;"'; ?>>
<? if ($options['copymode'] === 'move') : ?>
    <?= _('Ziel zum Verschieben auswählen') ?>
<? elseif ($options['copymode'] === 'copy') : ?>
    <?= _('Ziel zum Kopieren auswählen') ?>
<? elseif ($options['copymode'] === 'upload') : ?>
    <?= _('Wohin soll hochgeladen werden?') ?>
<? endif ?>

<div class="file_select_possibilities">
    <? if (isset($parent_folder) && ($parent_folder->isWritable($GLOBALS['user']->id) || count($parent_folder->getSubfolders()))): ?>
        <? if ($options['from_plugin']) : ?>
        <a href="<?= $controller->link_for('/choose_folder/' . $parent_folder->getId(), array_merge($options, ['to_plugin' => $options['from_plugin'] ])) ?>" data-dialog>
        <? else: ?>
        <a href="<?= $controller->link_for('/choose_folder/' . $parent_folder->getId(), $options) ?>" data-dialog>
        <? endif ?>
            <?= Icon::create('folder-parent', Icon::ROLE_CLICKABLE)->asImg(50) ?>
            <?= _('Aktueller Ordner') ?>
        </a>
    <? endif ?>
        <a href="<?= $controller->link_for('/choose_folder/' . Folder::findTopFolder($GLOBALS['user']->id)->getId(), $options) ?>" data-dialog>
            <?= Icon::create('files', Icon::ROLE_CLICKABLE)->asImg(50) ?>
            <?= _('Meine Dateien') ?>
        </a>
        <a href="<?= $controller->link_for('/choose_folder_from_course/', $options) ?>" data-dialog>
            <?= Icon::create('seminar', Icon::ROLE_CLICKABLE)->asImg(50) ?>
            <?= _('Meine Veranstaltungen') ?>
        </a>
        <a href="<?= $controller->link_for('/choose_folder_from_institute/', $options) ?>" data-dialog>
            <?= Icon::create('institute', Icon::ROLE_CLICKABLE)->asImg(50) ?>
            <?= _('Meine Einrichtungen') ?>
        </a>
    <? foreach (PluginManager::getInstance()->getPlugins('FilesystemPlugin') as $plugin) : ?>
        <? if ($plugin->isPersonalFileArea()) : ?>
            <? $nav = $plugin->getFileSelectNavigation() ?>
            <? if ($nav) : ?>
                <a href="<?= $controller->link_for('/choose_folder/', array_merge($options, ['to_plugin' => get_class($plugin)])) ?>" data-dialog>
                    <?= $nav->getImage()->asImg(50) ?>
                    <?= htmlReady($nav->getTitle()) ?>
                </a>
            <? endif ?>
        <? endif ?>
    <? endforeach ?>
    </div>


    <? if (!Request::isDialog()) : ?>

        <?
        if ($parent_folder) {
            $cancelUrl = (in_array($parent_folder->range_type,  ['course', 'institute']) ? $parent_folder->range_type . '/' : '') . 'files/index/' . $parent_folder->getId();
        } else {
            $cancelUrl = 'files_dashboard';
        }
        ?>

    <div>
        <?= Studip\LinkButton::create(_('Abbrechen'), $controller->url_for($cancelUrl)) ?>
    </div>
<? endif ?>
</div>
