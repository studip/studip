<?php
$permissions = [];
if ($file->isEditable($GLOBALS['user']->id)) {
    $permissions[] = 'w';
}
if ($file->isDownloadable($GLOBALS['user']->id)) {
    $permissions[] = 'dr';
}
?>
<tr class="<? if ($file->getLastChangeDate() > $last_visitdate && ($file->getUserId() !== $GLOBALS['user']->id)) echo 'new'; ?>"
    id="fileref_<?= htmlReady($table_id) ?>_<?= htmlReady($file->getId()) ?>"
    role="row"
    data-permissions="<?= implode($permissions) ?>">
    <? if ($show_bulk_checkboxes) : ?>
        <td>
            <? if ($file->isDownloadable($GLOBALS['user']->id)) : ?>
                <input type="checkbox"
                       class="studip-checkbox"
                       name="ids[]"
                       id="file_checkbox_<?= htmlReady($table_id) ?>_<?= htmlReady($file->getId()) ?>"
                       value="<?= htmlReady($file->getId()) ?>"
                       <?= in_array($file->getId(), (array) $marked_element_ids) ? 'checked' : '' ?>>
                <label for="file_checkbox_<?= htmlReady($table_id) ?>_<?= htmlReady($file->getId()) ?>"></label>
            <? endif ?>
        </td>
    <? endif ?>
    <td class="document-icon" data-sort-value="<?= crc32($file->getMimeType()) ?>">
        <? if ($file->isDownloadable($GLOBALS['user']->id)) : ?>
            <a href="<?= htmlReady($file->getDownloadURL()) ?>" target="_blank" rel="noopener noreferrer">
                <?= $file->getIcon(Icon::ROLE_CLICKABLE)->asImg(24) ?>
            </a>
        <? else : ?>
            <?= $file->getIcon(Icon::ROLE_INACTIVE)->asImg(24) ?>
        <? endif ?>
    </td>
    <td data-sort-value="<?= htmlReady($file->getFilename()) ?>">
        <? if ($file->isDownloadable($GLOBALS['user']->id)) : ?>
            <a href="<?= $controller->link_for("file/details/{$file->getId()}", ['file_navigation' => '1']) ?>" data-dialog>
                <?= htmlReady($file->getFilename()) ?>
            </a>
        <? else : ?>
            <?= htmlReady($file->getFilename()) ?>
        <? endif ?>

        <?php $terms = $file->getTermsOfUse() ?>
        <? if ($terms && !$terms->isDownloadable($topFolder->range_id, $topFolder->range_type, false)) : ?>
            <?= Icon::create('lock-locked', Icon::ROLE_INFO)->asImg(['title' => _('Das Herunterladen dieser Datei ist nur eingeschränkt möglich.')]) ?>
        <? endif ?>
    </td>
    <? $size = $file->getSize() ?>
    <td title="<?= number_format($size, 0, ',', '.') . ' Byte' ?>" data-sort-value="<?= htmlReady($size) ?>" class="responsive-hidden">
    <? if ($size !== null) : ?>
        <?= relSize($size, false) ?>
    <? endif ?>
    </td>
    <? if ($show_downloads) : ?>
        <? $downloads = $file->getDownloads() ?>
        <td data-sort-value="<?= htmlReady($downloads) ?>" class="responsive-hidden">
            <?= htmlReady($downloads) ?>
        </td>
    <? endif ?>
    <? $author_name = $file->getUserName() ?>
    <td data-sort-value="<?= htmlReady($author_name) ?>" class="responsive-hidden">
    <? if ($file->getUser() && $file->getUser()->id !== $GLOBALS['user']->id) : ?>
        <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $file->getUser()->username) ?>">
            <?= htmlReady($author_name) ?>
        </a>
    <? else: ?>
        <?= htmlReady($author_name) ?>
    <? endif; ?>
    </td>
    <? $chdate = $file->getLastChangeDate() ?>
    <td title="<?= strftime('%x %X', $chdate) ?>" data-sort-value="<?= htmlReady($chdate) ?>" class="responsive-hidden">
        <?= $chdate ? reltime($chdate) : "" ?>
    </td>
    <? foreach ($current_folder->getAdditionalColumns() as $index => $column_name) : ?>
        <td class="responsive-hidden"
            data-sort-value="<?= htmlReady($file->getAdditionalColumnOrderWeigh($index)) ?>">
        <? $content = $file->getContentForAdditionalColumn($index) ?>
        <? if ($content) : ?>
            <?= is_a($content, "Flexi_Template") ? $content->render() : $content ?>
        <? endif ?>
        </td>
    <? endforeach ?>
    <td class="actions">
        <?
        $actionMenu = $file->getActionMenu();
        if ($actionMenu) {
            echo $actionMenu->render();
        }
        ?>
    </td>
</tr>
