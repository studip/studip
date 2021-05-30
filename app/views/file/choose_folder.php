<?php
$options = array_filter([
    'to_plugin'   => Request::get('to_plugin'),
    'from_plugin' => Request::get('from_plugin'),
    'fileref_id'  => Request::getArray('fileref_id'),
    'isfolder'    => Request::get('isfolder'),
    'copymode'    => Request::get('copymode'),
], function ($value) {
    return $value !== null;
});

$headings = [
    'copy'   => _('Kopieren nach'),
    'move'   => _('Verschieben nach'),
    'upload' => _('Hochladen nach'),
];
$buttonLabels = [
    'copy'   => _('Hierher kopieren'),
    'move'   => _('Hierher verschieben'),
    'upload' => _('Hierher hochladen'),
];
?>

<div style="text-align: center; margin-bottom: 20px;">
    <?= $headings[$options['copymode']] ?>
    <?= Icon::create('folder-full', Icon::ROLE_INFO)->asImg(20, ['class' => 'text-bottom']) ?>
    <?= htmlReady($top_folder_name) ?>
</div>

<? /*if ($filesystemplugin && $filesystemplugin->hasSearch()) : ?>
    <form action="<?= $controller->url_for('/choose_file/' . $top_folder->parent_id) ?>" method="get" class="default" style="margin-bottom: 50px;">
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

<form action="#" method="post">
<? foreach ($options as $key => $value): ?>
    <?= addHiddenFields($key, $value) ?>
<? endforeach; ?>

<? if ($top_folder) : ?>
    <table class="default">
        <thead>
            <tr>
                <th width="25px"><?= _('Typ') ?></th>
                <th><?= _('Name') ?></th>
            </tr>
        </thead>
        <tbody>
        <? if (($top_folder->parent_id || $top_folder instanceof VirtualFolderType) && $top_folder->parent_id !== $top_folder->getId()) : ?>
            <tr>
                <td colspan="2">
                    <!-- neu -->
                    <button formaction="<?= $controller->link_for('/choose_folder/' . $top_folder->parent_id) ?>" class="undecorated" data-dialog>
                        <small><?= _('Ein Verzeichnis nach oben wechseln') ?></small>
                    </button>
                </td>
            </tr>
        <? endif ?>
        <? if ($top_folder->is_empty): ?>
            <tr>
                <td colspan="2" class="empty">
                    <?= _('Dieser Ordner ist leer') ?>
                </td>
            </tr>
        <? endif; ?>
    <? foreach ($top_folder->getSubfolders() as $subfolder) : ?>
        <? if (!$subfolder->isVisible($GLOBALS['user']->id)) continue; ?>
        <? if ($subfolder->isWritable($GLOBALS['user']->id)): ?>
            <tr>
                <td class="document-icon" data-sort-value="0">
                    <!-- neu -->
                    <button formaction="<?= $controller->link_for('/choose_folder/' . $subfolder->getId()) ?>" class="undecorated" data-dialog>
                    <? if ($subfolder->is_empty): ?>
                        <?= Icon::create('folder-empty')->asImg(24) ?>
                    <? else: ?>
                        <?= Icon::create('folder-full')->asImg(24) ?>
                    <? endif; ?>
                    </button>
                </td>
                <td>
                    <!-- neu -->
                    <button formaction="<?= $controller->link_for('/choose_folder/' . $subfolder->getId()) ?>" class="undecorated" data-dialog>
                        <?= htmlReady($subfolder->name) ?>
                    </button>

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
                <? if ($subfolder->is_empty): ?>
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
    </table>
<? endif; ?>

<?php
switch ($top_folder->range_type) {
    case 'user':
        $check = true;
        break;
    case 'course':
    case 'institute':
        $check = CoreDocuments::checkActivation($top_folder->range_id);
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
<? endif; ?>

<footer data-dialog-button>
<? if ($check && $top_folder->isWritable($GLOBALS['user']->id) && !in_array($top_folder->getId(), $options['fileref_id'])): ?>
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

    <!-- neu -->

    <?= Studip\Button::createAccept(
        $buttonLabels[$options['copymode']] ?: _('Auswählen'),
        $buttonOptions + [
            'formaction' => $controller->url_for('files/copyhandler/' . $top_folder->getId()),
        ]
    ) ?>
<? endif; ?>

<? if (Request::get('direct_parent')): ?>
    <!-- neu -->
    <?= Studip\Button::create(_('Zurück'), [
        'formaction'  => $controller->url_for('/choose_destination/' . $options['copymode']),
        'data-dialog' => 'size=auto',
    ]) ?>
<? elseif ($top_folder->range_type === 'course') : ?>
    <!-- neu -->
    <?= Studip\Button::create(_('Zurück'), [
        'formaction'  => $controller->url_for('/choose_folder_from_course'),
        'data-dialog' => '',
    ]) ?>
<? elseif($top_folder->range_type === 'institute'): ?>
    <!-- neu -->
    <?= Studip\Button::create(_('Zurück'), [
        'formaction'  => $controller->url_for('/choose_folder_from_institute'),
        'data-dialog' => '',
    ]) ?>
<? endif; ?>
</footer>
