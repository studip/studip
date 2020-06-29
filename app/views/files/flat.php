<?php
$show_downloads = in_array(Config::get()->DISPLAY_DOWNLOAD_COUNTER, ['always', 'flat']);
?>
<form method="post" action="<?= htmlReady($form_action) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default documents sortable-table flat <?= $enable_table_filter ? 'filter' : '' ?>" data-sortlist="[[<?= $show_downloads ? 6 : 5 ?>, 1]]" data-shiftcheck>
        <? if ($table_title) : ?>
            <caption><?= htmlReady($table_title) ?></caption>
        <? endif ?>
        <?= $this->render_partial(
            'files/_files_thead.php',
            [
                'show_downloads'       => $show_downloads,
                'show_bulk_checkboxes' => true
            ]
        ) ?>
        <tbody>
        <? if (count($files) === 0): ?>
            <tr>
                <td colspan="<?= $show_downloads ? 8 : 7 ?>" class="empty">
                    <?= _('Keine Dateien vorhanden.') ?>
                </td>
            </tr>
        <? else : ?>
            <? foreach ($files as $file_ref) : ?>
                <?= $this->render_partial('files/_fileref_tr', [
                    'file_ref'             => $file_ref,
                    'current_folder'       => $folders[$file_ref->folder_id] ?: $file_ref->folder->getTypedFolder(),
                    'show_downloads'       => $show_downloads,
                    'show_bulk_checkboxes' => true,
                    'flat_view'            => true
                ]) ?>
            <? endforeach ?>
        <? endif ?>
        </tbody>
        <? if ($GLOBALS['user']->id !== 'nobody') : ?>
            <?= $this->render_partial(
                'files/_flat_tfoot',
                [
                    'topFolder'      => $topFolder,
                    'show_downloads' => $show_downloads,
                    'pagination'     => $pagination
                ]
            ) ?>
        <? endif ?>
    </table>
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
