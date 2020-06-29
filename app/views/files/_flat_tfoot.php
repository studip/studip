<?
$writable = $writable || false;
if ($topFolder) {
    $writable = $topFolder->isWritable($GLOBALS['user']->id);
}
$table_selector = 'table.documents';
if ($table_id) {
    $table_selector .= '[data-table_id="' . htmlReady($table_id) . '"]';
}
?>
<tfoot>
    <tr>
        <td colspan="<?= $show_downloads ? 8 : 7 ?>">
            <span class="multibuttons">
                <?= Studip\Button::create(_('Herunterladen'), 'download', [
                    'data-activates-condition' => $table_selector . ' tr[data-permissions*=d] :checkbox:checked'
                ]) ?>
                <? if ($writable): ?>
                    <?= Studip\Button::create(
                        _('Lizenz ändern'),
                        'change_license',
                        [
                            'formaction' => $controller->url_for('file/edit_license/bulk'),
                            'data-dialog' => '',
                            'data-activates-condition' => $table_selector . ' tr[data-permissions*=w] :checkbox:checked'
                        ]
                    ) ?>
                    <?= Studip\Button::create(_('Verschieben'), 'move', [
                        'formaction'  => $controller->url_for('file/choose_destination/move/bulk'),
                        'data-dialog' => '',
                        'data-activates-condition' => $table_selector . ' tr[data-permissions*=w] :checkbox:checked'
                    ]) ?>
                <? endif ?>
                <?= Studip\Button::create(_('Kopieren'), 'copy', [
                    'formaction'  => $controller->url_for('file/choose_destination/copy/bulk'),
                    'data-dialog' => ''
                ]) ?>
                <? if ($writable): ?>
                    <?= Studip\Button::create(_('Löschen'), 'delete', [
                        'data-confirm'             => _('Soll die Auswahl wirklich gelöscht werden?'),
                        'data-activates-condition' => $table_selector . ' tr[data-permissions*=w] :checkbox:checked'
                    ]) ?>
                <? endif ?>
            </span>
            <? if (is_array($pagination)) : ?>
                <div class="pagination-wrapper">
                    <?
                    $page = $pagination[0];
                    $amount = $pagination[1];
                    $page_size = $pagination[2];
                    $link_closure = $pagination[3];
                    ?>
                    <?= Pagination::create($amount, $page, $page_size)->asLinks($link_closure) ?>
                </div>
            <? endif ?>
        </td>
    </tr>
</tfoot>
