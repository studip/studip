<?php
$options = [];
if (Request::get('from_plugin')) {
    $options['from_plugin'] = Request::get('from_plugin');
}
if (Request::get('to_plugin')) {
    $options['to_plugin'] = Request::get('to_plugin');
}
if (Request::get('to_folder_id')) {
    $options['to_folder_id'] = Request::get('to_folder_id');
}
?>

<div style="text-align: center; margin-bottom: 20px;">
    <?= _('Kopieren nach') ?>
    <?= Icon::create('folder-full', Icon::ROLE_INFO)->asImg(20, ['class' => 'text-bottom']) ?>
    <?= htmlReady($to_folder_name) ?>
</div>

<? if ($filesystemplugin && $filesystemplugin->hasSearch()) : ?>
    <form action="<?= $controller->url_for('file/choose_file/' . $top_folder->parent_id) ?>" class="default" data-dialog style="margin-bottom: 50px;" id="file_search">
    <? foreach ($options as $key => $value) : ?>
        <input type="hidden" name="<?= htmlReady($key) ?>" value="<?= htmlReady($value) ?>">
    <? endforeach; ?>
        <? $request_parameter = Request::getArray('parameter') ?>
        <input type="text" name="search" value="<?= htmlReady(Request::get('search')) ?>"
               placeholder="<?= _('Suche nach ...') ?>" style="max-width: 100%">

    <? foreach ((array) $filesystemplugin->getSearchParameters() as $parameter) : ?>
        <label>
        <? if ($parameter['type'] === 'text'): ?>
            <?= htmlReady($parameter['label']) ?>
            <input type="text" name="parameter[<?= htmlReady($parameter['name']) ?>]"
                   value="<?= htmlReady($request_parameter[$parameter['name']]) ?>"
                   placeholder="<?= htmlReady($parameter['placeholder']) ?>">
        <? elseif ($parameter['type'] === 'select'): ?>
            <?= htmlReady($parameter['label']) ?>
            <select name="parameter[<?= htmlReady($parameter['name']) ?>]">
            <? foreach ($parameter['options'] as $index => $option) : ?>
                <option value="<?= htmlReady($index) ?>" <? if ($index === $request_parameter[$parameter['name']]) echo 'selected'; ?>>
                    <?= htmlReady($option) ?>
                </option>
            <? endforeach; ?>
            </select>
        <? elseif ($parameter['type'] === 'checkbox'): ?>
            <input type="checkbox" name="parameter[<?= htmlReady($parameter['name']) ?>]" value="1"
                   <? if ($request_parameter[$parameter['name']]) echo 'checked'; ?>>
            <?= htmlReady($parameter['label']) ?>
        <? endif; ?>
        </label>
    <? endforeach; ?>
    </form>

<? endif; ?>

<? if ($top_folder): ?>
<table class="default">
    <thead>
        <tr>
            <th><?= _('Typ') ?></th>
            <th><?= _('Name') ?></th>
        </tr>
    </thead>
<? if ($top_folder->parent_id && ($top_folder->parent_id !== $top_folder->id)) : ?>
    <tbody>
        <tr>
            <td colspan="2">
                <a href="<?= $controller->url_for('file/choose_file/' . $top_folder->parent_id, $options) ?>" title="<?= _('Ein Verzeichnis nach oben wechseln') ?>" data-dialog>
                    <?= _('Ein Verzeichnis nach oben wechseln') ?>
                </a>
            </td>
        </tr>
    </tbody>
<? endif ?>
<? if (count($top_folder->getSubfolders()) + count($top_folder->getFiles()) === 0): ?>
    <tbody>
        <tr>
            <td colspan="2" class="empty">
                <?= _('Dieser Ordner ist leer') ?>
            </td>
        </tr>
    </tbody>
<? else : ?>
    <tbody>
    <? foreach ($top_folder->getSubfolders() as $subfolder) : ?>
        <tr <? if ($full_access) printf('data-file="%s"', $subfolder->id) ?> <? if ($full_access) printf('data-folder="%s"', $subfolder->id); ?>>
            <td class="document-icon" data-sort-value="0">
            <? if ($subfolder->isReadable($GLOBALS['user']->id)) : ?>
                <a href="<?= $controller->link_for('file/choose_file/' . $subfolder->id, $options) ?>" data-dialog>
            <? endif ?>
            <? if ($is_empty): ?>
                <?= Icon::create('folder-empty')->asImg(24) ?>
            <? else: ?>
                <?= Icon::create('folder-full')->asImg(24) ?>
            <? endif; ?>
            <? if ($subfolder->isReadable($GLOBALS['user']->id)) : ?>
                </a>
            <? endif ?>
            </td>
            <td>
            <? if ($subfolder->isReadable($GLOBALS['user']->id)) : ?>
                <a href="<?= $controller->link_for('file/choose_file/' . $subfolder->id, $options) ?>" data-dialog>
                    <?= htmlReady($subfolder->name) ?>
                </a>
            <? else: ?>
                <?= htmlReady($subfolder->name) ?>
            <? endif ?>
            <? if ($subfolder->description): ?>
                <small class="responsive-hidden"><?= htmlReady($subfolder->description) ?></small>
            <? endif; ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
    <? if (count($top_folder->getFiles())) : ?>
        <tbody>
        <? foreach ($top_folder->getFiles() as $fileref) : ?>
            <tr>
                <td class="document-icon" data-sort-value="1">
                <? if ($top_folder->isFileDownloadable($fileref, $GLOBALS['user']->id)): ?>
                    <form action="<?= $controller->link_for('file/choose_file/' . $top_folder->id, $options) ?>" method="post" data-dialog>
                        <input type="hidden" name="file_id" value="<?= htmlReady($fileref->id) ?>">
                        <a href="#" onclick="jQuery(this).closest('form').submit(); return false;">
                            <?= FileManager::getIconForFileRef($fileref)->asImg(24) ?>
                        </a>
                    </form>
                <? else: ?>
                    <?= FileManager::getIconForFileRef($fileref, Icon::ROLE_INACTIVE)->asImg(24) ?>
                <? endif ?>
                </td>
                <td>
                <? if ($top_folder->isFileDownloadable($fileref, $GLOBALS['user']->id)) : ?>
                    <form action="<?= $controller->link_for('file/choose_file/' . $top_folder->id, $options) ?>" method="post" data-dialog>
                        <input type="hidden" name="file_id" value="<?= htmlReady($fileref->id) ?>">
                        <a href="#" onclick="jQuery(this).closest('form').submit(); return false;">
                            <?= htmlReady($fileref->name) ?>
                            <? if ($fileref->description) : ?>
                                <div style="color: grey; font-size: 0.8em;"><?= htmlReady($fileref->description) ?></div>
                            <? endif ?>
                        </a>
                    </form>
                <? else: ?>
                    <?= htmlReady($fileref->name) ?>
                    <? if ($fileref->description): ?>
                        <div style="color: grey; font-size: 0.8em;"><?= htmlReady($fileref->description) ?></div>
                    <? endif ?>
                <? endif; ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
    <? endif ?>
<? endif ?>
</table>
<? endif ?>

<?php
$options = [];
if (Request::get('to_plugin')) {
    $options['to_plugin'] = Request::get('to_plugin');
}
?>
<footer data-dialog-button>
    <?= Studip\LinkButton::create(
        _('Zurück'),
        $controller->url_for('/add_files_window/' . Request::get('to_folder_id'), $options),
        ['data-dialog' => 1]
    ) ?>
<? if ($filesystemplugin && $filesystemplugin->hasSearch()) : ?>
    <?= Studip\Button::create(
        _('Suche starten'),
        'startsearch',
        ['form' => 'file_search']
    ) ?>
<? endif ?>
</footer>
