<table class="default documents sortable-table flat dashboad-documents-list" data-sortlist="[[5, 1]]" data-element-id="<?= htmlReady($widget->getElement()->id) ?>">
    <?= $this->render_partial('_base-list-thead.php') ?>

    <tbody>
        <? if (count($files) === 0) : ?>
            <tr>
                <td colspan="8" class="empty">
                    <?= _('Keine Dateien vorhanden.') ?>
                </td>
            </tr>
        <? else: ?>
            <? foreach ($files as $fileRef) : ?>
                <?= $this->render_partial('_base-list-tr', [
                    'fileRef' => $fileRef,
                    'currentFolder' => isset($folders[$fileRef->folder_id]) ? $folders[$fileRef->folder_id] : $fileRef->getFolderType(),
                ]) ?>
            <? endforeach ?>
        <? endif ?>
    </tbody>
</table>
