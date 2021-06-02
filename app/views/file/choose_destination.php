<?php
$options = array_filter([
    'from_plugin'   => Request::get('from_plugin'),
    'to_folder_id'  => Request::get('to_folder_id'),
    'copymode'      => Request::get('copymode', $copymode),
    'isfolder'      => Request::get('isfolder'),
    'fileref_id'    => Request::getArray('fileref_id') ?: $fileref_id,
    'direct_parent' => true,
], function ($value) {
    return $value !== null;
});
?>

<form action="#" method="post" class="files_source_selector" data-dialog <? if ($hidden) echo ' style="display: none;"'; ?>>
    <input type="hidden" name="webkitbugfix" value="1">
<? foreach ($options as $key => $value): ?>
    <?= addHiddenFields($key, $value) ?>
<? endforeach; ?>

<? if ($options['copymode'] === 'move') : ?>
    <?= _('Ziel zum Verschieben auswählen') ?>
<? elseif ($options['copymode'] === 'copy') : ?>
    <?= _('Ziel zum Kopieren auswählen') ?>
<? elseif ($options['copymode'] === 'upload') : ?>
    <?= _('Wohin soll hochgeladen werden?') ?>
<? endif ?>

    <div class="file_select_possibilities">
        <div>
        <? if (isset($parent_folder) && ($parent_folder->isWritable($GLOBALS['user']->id) || count($parent_folder->getSubfolders()))): ?>
            <div class="clickable">
                <?= Icon::create('folder-parent', Icon::ROLE_CLICKABLE)->asInput(50, ['formaction' => $controller->url_for('/choose_folder/' . $parent_folder->getId()), 'to_plugin' => $options['from_plugin']]) ?>
                <button
                    class="undecorated"
                    formaction="<?= $controller->link_for('/choose_folder/' . $parent_folder->getId()) ?>" <? if ($options['from_plugin']): ?> name="to_plugin" value="<?= htmlReady($options['from_plugin']) ?>"<? endif; ?>>
                    <?= _('Aktueller Ordner') ?>
                </button>
            </div>
        <? endif ?>
            <div class="clickable">
                <?= Icon::create('files')->asInput(50, ['formaction' => $controller->url_for('/choose_folder/' . Folder::findTopFolder($GLOBALS['user']->id)->getId())]) ?>
                <button
                    class="undecorated"
                    formaction="<?= $controller->link_for('/choose_folder/' . Folder::findTopFolder($GLOBALS['user']->id)->getId()) ?>">

                    <?= _('Persönlicher Dateibereich') ?>
                </button>
            </div>

            <div class="clickable">
                <?= Icon::create('seminar')->asinput(50, ['formaction' => $controller->url_for('/choose_folder_from_course')]) ?>
                <button class="undecorated"
                        formaction="<?= $controller->link_for('/choose_folder_from_course') ?>">
                    <?= _('Meine Veranstaltungen') ?>
                </button>
            </div>

            <div class="clickable">
                <?= Icon::create('institute')->asInput(50, ['formaction' => $controller->url_for('/choose_folder_from_institute')]) ?>
                <button class="undecorated"
                        formaction="<?= $controller->link_for('/choose_folder_from_institute') ?>">
                    <?= _('Meine Einrichtungen') ?>
                </button>
            </div>

        <? foreach (PluginManager::getInstance()->getPlugins('FilesystemPlugin') as $plugin) : ?>
            <? if ($plugin->isPersonalFileArea()) : ?>
                <? $nav = $plugin->getFileSelectNavigation() ?>
                <? if ($nav) : ?>
                    <div class="clickable">
                        <?= $nav->getImage()->asInput(50, ['formaction' => $controller->url_for('/choose_folder'), 'name' => 'to_plugin', 'value' => get_class($plugin)]) ?>
                        <button formaction="<?= $controller->link_for('/choose_folder') ?>"
                                type="submit"
                                class="undecorated"
                                name="to_plugin"
                                value="<?= htmlReady(get_class($plugin)) ?>">
                            <?= htmlReady($nav->getTitle()) ?>
                        </button>
                    </div>
                <? endif ?>
            <? endif ?>
        <? endforeach ?>
        </div>
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
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for($cancelUrl)) ?>
    </div>
<? endif ?>
</form>
