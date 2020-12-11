<?php
$show_downloads = in_array(Config::get()->DISPLAY_DOWNLOAD_COUNTER, ['always', 'flat']);
$vue_files = [];
foreach ($files as $file) {
    if ($file->isVisible($GLOBALS['user']->id)) {
        $vue_files[] = FilesystemVueDataManager::getFileVueData($file, $file->getFolderType(), $last_visitdate);
    }
}
$vue_files = array_values(SimpleCollection::createFromArray($vue_files)->orderBy('chdate desc')->toArray());

$topFolder = new StandardFolder();
$vue_topFolder = [
    'description' => $topFolder->getDescriptionTemplate(),
    'additionalColumns' => $topFolder->getAdditionalColumns(),
    'buttons' => null
];
if (is_a($vue_topFolder['description'], "Flexi_Template")) {
    $vue_topFolder['description'] = $vue_topFolder['description']->render();
}
$vue_topFolder['buttons'] = '<span class="multibuttons">';
$vue_topFolder['buttons'] .= Studip\Button::create(_('Herunterladen'), 'download', [
    'data-activates-condition' => 'table.documents tr[data-permissions*=d] :checkbox:checked'
]);
if ($topFolder->isWritable($GLOBALS['user']->id)) {
    $vue_topFolder['buttons'] .= Studip\Button::create(_('Verschieben'), 'move', [
        'formaction'  => $controller->url_for('file/choose_destination/move/bulk'),
        'data-dialog' => 'size=auto',
        'data-activates-condition' => 'table.documents tr[data-permissions*=w] :checkbox:checked'
    ]);
}
$vue_topFolder['buttons'] .= Studip\Button::create(_('Kopieren'), 'copy', [
    'formaction'  => $controller->url_for('file/choose_destination/copy/bulk'),
    'data-dialog' => 'size=auto',
    'data-activates-condition' => 'table.documents tr[data-permissions*=r] :checkbox:checked'
]);
if ($topFolder->isWritable($GLOBALS['user']->id)) {
    $vue_topFolder['buttons'] .= Studip\Button::create(_('Löschen'), 'delete', [
        'data-confirm'             => _('Soll die Auswahl wirklich gelöscht werden?'),
        'data-activates-condition' => 'table.documents tr[data-permissions*=w] :checkbox:checked'
    ]);
}
$vue_topFolder['buttons'] .= '</span>';
foreach ($topFolder->getAdditionalActionButtons() as $button) {
    $vue_topFolder['buttons'] .= $button;
}
?>
<form id="files_table_form"
      method="post"
      action="<?= htmlReady($form_action) ?>"
      data-files="<?= htmlReady(json_encode($vue_files)) ?>"
      data-topfolder="<?= htmlReady(json_encode((array) $vue_topFolder)) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <files-table :showdownloads="<?= $show_downloads ? "true" : "false" ?>"
                 :breadcrumbs="breadcrumbs"
                 :files="files"
                 :folders="folders"
                 :topfolder="topfolder"
                 enable_table_filter="<?= $enable_table_filter ? 'true' : 'false' ?>"
                 table_title="<?= htmlReady($table_title) ?>"
                 pagination="<?= htmlReady($pagination_html) ?>"
                 :initial_sort="{sortedBy:'chdate',sortDirection:'desc'}"
    ></files-table>
</form>

<? ob_start(); ?>
<? if ($enable_table_filter) : ?>
<div align="center">
<input class="tablesorterfilter" placeholder="<?= _('Name oder Autor/-in') ?>" data-column="2,4" type="search" style="width: 100%; margin-bottom: 5px;"><br>
</div>
<? endif ?>
<?
if ($show_default_sidebar) {
    if ($enable_table_filter) {
        $content = ob_get_clean();
        $widget = new SidebarWidget();
        $widget->setTitle(_('Filter'));
        $widget->addElement(new WidgetElement($content));
        Sidebar::get()->addWidget($widget);
    } else {
        ob_get_clean();
    }

    $views = new ViewsWidget();
    $views->addLink(
        _('Ordneransicht'),
        $controller->url_for(($range_type ? $range_type . '/' : '') . 'files/index'),
        null,
        [],
        'index'
    );
    $views->addLink(
        _('Alle Dateien'),
        $controller->url_for(($range_type ? $range_type.'/' : '') . 'files/flat'),
        null,
        [],
        'flat'
    )->setActive(true);
    Sidebar::get()->addWidget($views);
} else {
    ob_get_clean();
}
