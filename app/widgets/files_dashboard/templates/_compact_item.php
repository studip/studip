<?php
$fileRef = $_compact_item;
if (!$currentFolder = $folders[$fileRef->folder_id]) {
    $currentFolder = $fileRef->folder->getTypedFolder();
}
$userId = $GLOBALS['user']->id;
$elementId = sprintf(
    'widget-%s-fileref-%s',
    $widget->getElement()->getId(),
    $fileRef->id
);
?>

<li id="<?= $elementId?>" <? printf('data-file="%s"', $fileRef->id) ?>>

    <span class="document-icon">
        <? if ($currentFolder->isFileDownloadable($fileRef, $userId)) : ?>
            <a href="<?= htmlReady($fileRef->getDownloadURL('force_download')) ?>" target="_blank" rel="noopener noreferrer">
                <?= FileManager::getIconForFileRef($fileRef)->asImg(24) ?>
            </a>
        <? else : ?>
            <?= FileManager::getIconForFileRef($fileRef, Icon::ROLE_INACTIVE)->asImg(24) ?>
        <? endif ?>
    </span>

    <span class="document-data">
        <span class="document-name">
            <a href="<?= htmlReady($controller->url_for('file/details/'.$fileRef->id)) ?>" data-dialog="">
                <?= htmlReady($fileRef->name) ?>

                <? if ($fileRef->terms_of_use && $fileRef->terms_of_use->download_condition > 0): ?>
                    <?= Icon::create('lock-locked', ICON::ROLE_INACTIVE)->asImg([
                        'class' => 'text-top document-restricted',
                        'title' => _('Das Herunterladen dieser Datei ist nur eingeschränkt möglich.'),
                    ]) ?>
                <? endif; ?>
            </a>
        </span>

        <span class="document-range document-range-type-<?= $currentFolder->range_type ?>">
            <?= $this->render_partial('_compact_item_range', compact('currentFolder')) ?>
        </span>

        <span class="document-size" title="<?= number_format($fileRef->size, 0, ',', '.').' Byte' ?>">
            <? if ($fileRef->is_link) : ?>
                <?= _('Weblink') ?>
            <? else : ?>
                <?= relSize($fileRef->size, false) ?>
            <? endif ?>
        </span>

        <span class="document-author">
            <? if ($fileRef->owner): ?>
                <a href="<?= URLHelper::getURL('dispatch.php/profile?username='.$fileRef->owner->username) ?>">
                    <?= htmlReady($fileRef->author_name) ?>
                </a>
            <? else : ?>
                <?= htmlReady($fileRef->author_name) ?>
            <? endif ?>
        </span>

        <span class="document-chdate" title="<?= strftime('%x %X', $fileRef->chdate) ?>">
            <?= $fileRef->chdate ? reltime($fileRef->chdate) : '' ?>
        </span>
    </span>

    <span class="document-actions">
        <?= $this->render_partial('_actions', compact('fileRef', 'currentFolder', 'userId')) ?>
    </span>
</li>
