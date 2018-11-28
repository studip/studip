<?php
$options = [];
if (Request::get('to_plugin')) {
    $options['to_plugin'] = Request::get('to_plugin');
}
if (Request::get('from_plugin')) {
    $options['from_plugin'] = Request::get('from_plugin');
}
if (Request::getArray('fileref_id')) {
    $options['fileref_id'] = Request::getArray('fileref_id');
}
if (Request::get('isfolder')) {
    $options['isfolder'] = Request::get('isfolder');
}
if (Request::get('copymode')) {
    $options['copymode'] = Request::get('copymode');
}

$headings = [ 'copy' => _('Kopieren nach'), 'move' => _('Verschieben nach'), 'upload' => _('Hochladen nach')];
$buttonLabels = [ 'copy' => _('Hierher kopieren'), 'move' => _('Hierher verschieben'), 'upload' => _('Hierher hochladen')];
?>

<div style="text-align: center; margin-bottom: 20px;">
    <?= $headings[$options['copymode']] ?>
    <?= Icon::create('folder-full', Icon::ROLE_INFO)->asImg(20, ['class' => 'text-bottom']) ?>
    <?= htmlReady($top_folder_name) ?>
</div>

<? /*if ($filesystemplugin && $filesystemplugin->hasSearch()) : ?>
    <form action="<?= $controller->url_for('/choose_file/' . $top_folder->parent_id) ?>" method="get" class="default" data-dialog style="margin-bottom: 50px;">
        <? foreach ($options as $key => $value) : ?>
            <input type="hidden" name="<?= htmlReady($key) ?>" value="<?= htmlReady($value) ?>">
        <? endforeach ?>
        <? $request_parameter = Request::getArray("parameter") ?>
        <input type="text" name="search" value="<?= htmlReady(Request::get("search")) ?>" placeholder="<?= _("Suche nach ...") ?>" style="max-width: 100%;">

        <? foreach ((array) $filesystemplugin->getSearchParameters() as $parameter) : ?>
            <label>
                <? switch ($parameter['type']) {
                    case "text": ?>
                        <?= htmlReady($parameter['label']) ?>
                        <input type="text" name="parameter[<?= htmlReady($parameter['name']) ?>]" value="<?= htmlReady($request_parameter[$parameter['name']]) ?>" placeholder="<?= htmlReady($parameter['placeholder']) ?>">
                        <? break ?>
                    <? case "select": ?>
                        <?= htmlReady($parameter['label']) ?>
                        <select name="parameter[<?= htmlReady($parameter['name']) ?>]">
                            <? foreach ($parameter['options'] as $index => $option) : ?>
                                <option value="<?= htmlReady($index) ?>"<?= ($index === $request_parameter[$parameter['name']] ? " selected" : "") ?>><?= htmlReady($option) ?></option>
                            <? endforeach ?>
                        </select>
                        <? break ?>
                    <? case "checkbox": ?>
                        <input type="checkbox" name="parameter[<?= htmlReady($parameter['name']) ?>]" value="1"<?= $request_parameter[$parameter['name']] ? " checked" : "" ?>>
                        <?= htmlReady($parameter['label']) ?>
                        <? break ?>
                <? } ?>
            </label>
        <? endforeach ?>
    </form>

<? endif*/ ?>

<? if ($top_folder) : ?>
<table class="default">
    <thead>
        <tr>
            <th width="25px"><?= _('Typ') ?></th>
            <th><?= _('Name') ?></th>
        </tr>
    </thead>
<? if (($top_folder->parent_id || $top_folder instanceof VirtualFolderType) && ($top_folder->parent_id !== $top_folder->getId())) : ?>
    <tbody>
        <tr>
            <td colspan="2">
                <a href="<?= $controller->url_for('/choose_folder/' . $top_folder->parent_id, $options) ?>" title="<?= _('Ein Verzeichnis nach oben wechseln') ?>" data-dialog>
                    <small><?= _('Ein Verzeichnis nach oben wechseln') ?></small>
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
    <? foreach ($top_folder->getSubfolders() as $subfolder) : ?>
        <? if ($subfolder->isWritable($GLOBALS['user']->id)): ?>
        <tr <? if ($full_access) printf('data-file="%s"', $subfolder->getId()) ?> <? if ($full_access) printf('data-folder="%s"', $subfolder->getId()); ?>>
            <td class="document-icon" data-sort-value="0">
                <a href="<?= $controller->link_for('/choose_folder/' . $subfolder->getId(), $options) ?>"
                   data-dialog>
                    <? if ($is_empty): ?>
                        <?= Icon::create('folder-empty', Icon::ROLE_CLICKABLE)->asImg(24) ?>
                    <? else: ?>
                        <?= Icon::create('folder-full', Icon::ROLE_CLICKABLE)->asImg(24) ?>
                    <? endif; ?>
                </a>
            </td>
            <td>
                <a href="<?= $controller->link_for('/choose_folder/' . $subfolder->getId(), $options) ?>"
                   data-dialog>
                    <?= htmlReady($subfolder->name) ?>
                </a>
                <? if ($subfolder->description): ?>
                    <small class="responsive-hidden">
                        <?= htmlReady($subfolder->description) ?>
                    </small>
                <? endif; ?>
            </td>
        </tr>
        <? else : ?>
        <tr>
            <td class="document-icon" data-sort-value="0">
            <? if ($is_empty): ?>
                <?= Icon::create('folder-empty+decline', Icon::ROLE_INFO)->asImg(24) ?>
            <? else: ?>
                <?= Icon::create('folder-full+decline', Icon::ROLE_INFO)->asImg(24) ?>
            <? endif ?>
            </td>
            <td>
                <?= htmlReady($subfolder->name) ?>
            <? if ($subfolder->description): ?>
                <small class="responsive-hidden">
                    <?= htmlReady($subfolder->description) ?>
                </small>
            <? endif; ?>
            </td>
        </tr>
        <? endif ?>
    <? endforeach ?>
    </tbody>
<? endif; ?>
</table>
<? endif; ?>

<?php
$mods = new Modules();
switch ($top_folder->range_type) {
    case 'user':
        $check = true;
        break;
    case 'course':
    case 'institute':
        $check = $mods->getStatus('documents', $top_folder->range_id) > 0;
        break;
    default:
        $check = is_numeric($top_folder->range_type);
        break;
}
?>

<? if (!$check): ?>
    <? if ($top_folder->range_type == 'course') : ?>
        <?= MessageBox::error(_('Der Dateibereich ist für diese Veranstaltung nicht aktiviert.')) ?>
    <? elseif($top_folder->range_type == 'institute'): ?>
        <?= MessageBox::error(_('Der Dateibereich ist für diese Einrichtung nicht aktiviert.')) ?>
    <? endif; ?>
<? elseif ($top_folder->isWritable($GLOBALS['user']->id) && $top_folder->getId() !== $options['fileref_id']): ?>
    <footer data-dialog-button>
        <?
        if ($options['copymode'] === 'upload') {
            $buttonOptions = [
                'data-dialog' => 'size=auto',
                'onclick' => 'STUDIP.Files.openAddFilesWindow("'.$top_folder->getId().'"); return false;',
            ];
        } else {
            $buttonOptions = [];
        }
        ?>
        <?= Studip\LinkButton::createAccept(
            $buttonLabels[$options['copymode']] ?: _('Auswählen'),
            $controller->url_for('files/copyhandler/' . $top_folder->getId(), [
                'from_plugin'     => $options['from_plugin'],
                'to_plugin'  => $options['to_plugin'],
                'fileref_id' => $options['fileref_id'],
                'isfolder'   => $options['isfolder'],
                'copymode'   => $options['copymode']
            ]),
            $buttonOptions
        ) ?>
    </footer>
<? endif; ?>

<footer data-dialog-button>
<? if (Request::get('direct_parent')): ?>
    <?= Studip\LinkButton::create(
        _('Zurück'),
        $controller->url_for('/choose_destination/' . $options['copymode'] , $options),
        ['data-dialog' => 'size=auto']
    ) ?>
<? elseif ($top_folder->range_type === 'course') : ?>
    <?= Studip\LinkButton::create(
        _('Zurück'),
        $controller->url_for('/choose_folder_from_course/', $options),
        ['data-dialog' => '']
    ) ?>
<? elseif($top_folder->range_type === 'institute'): ?>
    <?= Studip\LinkButton::create(
        _('Zurück'),
        $controller->url_for('/choose_folder_from_institute/', $options),
        ['data-dialog' => '']
    ) ?>
<? endif; ?>
</footer>
