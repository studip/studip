<?php
$userId = $GLOBALS['user']->id;
?>
<tr id="fileref_<?= htmlReady($fileRef->id) ?>">

    <td class="document-icon" data-sort-value="1">
        <? if ($currentFolder->isFileDownloadable($fileRef, $userId)) : ?>
            <a href="<?= htmlReady($fileRef->getDownloadURL('force_download')) ?>" target="_blank" rel="noopener noreferrer">
                <?= FileManager::getIconForFileRef($fileRef)->asImg(24) ?>
            </a>
        <? else : ?>
            <?= FileManager::getIconForFileRef($fileRef, Icon::ROLE_INACTIVE)->asImg(24) ?>
        <? endif ?>
    </td>

    <td data-sort-value="<?= htmlReady($fileRef->name) ?>">
        <? if ($currentFolder->isFileDownloadable($fileRef, $userId)) : ?>
            <a href="<?= htmlReady($controller->url_for('file/details/'.$fileRef->id)) ?>"
               data-dialog="">
                <?= htmlReady($fileRef->name) ?>

                <? if ($fileRef->terms_of_use && $fileRef->terms_of_use->download_condition > 0) : ?>
                    <?= Icon::create('lock-locked', ICON::ROLE_INACTIVE)->asImg([
                        'class' => 'text-top',
                        'title' => _('Das Herunterladen dieser Datei ist nur eingeschränkt möglich.')]) ?>
                <? endif ?>
            </a>
        <? else : ?>
            <?= htmlReady($fileRef->name) ?>

            <? if ($fileRef->terms_of_use && $fileRef->terms_of_use->download_condition > 0) : ?>
                <?= Icon::create('lock-locked', Icon::ROLE_INFO)->asImg([
                    'class' => 'text-top',
                    'title' => _('Das Herunterladen dieser Datei ist nur eingeschränkt möglich.')]) ?>
            <? endif; ?>
        <? endif ?>
    </td>

    <td title="<?= number_format($fileRef->size, 0, ',', '.') . ' Byte' ?>"
        data-sort-value="<?= $fileRef->size ?>">
        <? if ($fileRef->is_link) : ?>
            <?= _('Weblink') ?>
        <? else : ?>
            <?= relSize($fileRef->size, false) ?>
        <? endif ?>
    </td>

    <td data-sort-value="<?= htmlReady($fileRef->author_name) ?>">
        <? if ($fileRef->user_id !== $userId && $fileRef->owner) : ?>
            <a href="<?= URLHelper::getURL('dispatch.php/profile?username=' . $fileRef->owner->username) ?>">
                <?= htmlReady($fileRef->author_name) ?>
            </a>
        <? else: ?>
            <?= htmlReady($fileRef->author_name) ?>
        <? endif ?>
    </td>

    <? $rangeLabel = $widget->getRangeLabel($currentFolder) ?: _('Zum Speicherort') ?>
    <td data-sort-value="<?= htmlReady($rangeLabel) ?>">
        <? if ($rangeLink = $controller->getRangeLink($currentFolder)) : ?>
            <a href="<?= $rangeLink  ?>">
                <?= htmlReady($rangeLabel) ?>
            </a>
        <? else: ?>
            <?= htmlReady($rangeLabel) ?>
        <? endif ?>
    </td>

    <td title="<?= strftime('%x %X', $fileRef->chdate) ?>"
        data-sort-value="<?= $fileRef->chdate ?>">
        <?= $fileRef->chdate ? reltime($fileRef->chdate) : "" ?>
    </td>

    <?
    if ($currentFolder->range_type === 'course')  {
        $course = $currentFolder->course;
        $startSemester = $course->start_semester;
        $endSemester = $course->end_semester;
    } else {
        $startSemester = $endSemester = \Semester::findByTimestamp($fileRef->chdate);
    }
    ?>
    <td data-sort-value="<?= (int) $startSemester->beginn ?>">
        <? if (!$endSemester || $startSemester === $endSemester) : ?>
            <?= htmlReady($startSemester->name) ?>
        <? else : ?>
            <?= htmlReady($startSemester->name) ?> - <?= htmlReady($endSemester->name) ?>
        <? endif ?>
    </td>

    <td class="actions">
        <?= $this->render_partial('_actions', compact('fileRef', 'currentFolder', 'userId'))  ?>
    </td>
</tr>
