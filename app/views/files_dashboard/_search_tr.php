<tr id="fileref_<?= htmlReady($searchResult['fileRef']->id) ?>">

    <td class="files-search-icon">
        <? if ($searchResult['folder']->isFileDownloadable($searchResult['fileRef'], $user->id)) : ?>
            <a href="<?= htmlReady($searchResult['fileRef']->getDownloadURL('force_download')) ?>" target="_blank" rel="noopener noreferrer">
                <?= FileManager::getIconForFileRef($searchResult['fileRef'])->asImg(24) ?>
            </a>
        <? else : ?>
            <?= FileManager::getIconForFileRef($searchResult['fileRef'], Icon::ROLE_INACTIVE)->asImg(24) ?>
        <? endif ?>
    </td>

    <td>
        <? if ($searchResult['folder']->isFileDownloadable($searchResult['fileRef'], $user->id)) : ?>
            <a href="<?= htmlReady($controller->url_for('file/details/'.$searchResult['fileRef']->id)) ?>" data-dialog="">
                <? if (mb_strlen(($searchResult['fileRef']->name))) : ?>
                    <?= $controller->markPhrase($searchResult['fileRef']->name, $query->getQuery()) ?>
                <? else : ?>
                    <i><?= _('kein Titel') ?></i>
                <? endif ?>

                <? if ($searchResult['fileRef']->terms_of_use &&
                       $searchResult['fileRef']->terms_of_use->download_condition > 0) : ?>
                    <?= Icon::create('lock-locked', ICON::ROLE_INACTIVE)->asImg([
                        'class' => 'text-top',
                        'title' => _('Das Herunterladen dieser Datei ist nur eingeschränkt möglich.')]) ?>
                <? endif; ?>
            </a>
        <? else : ?>
            <? if (mb_strlen(($searchResult['fileRef']->name))) : ?>
                <?= $controller->markPhrase($searchResult['fileRef']->name, $query->getQuery()) ?>
            <? else : ?>
                <i><?= _('kein Titel') ?></i>
            <? endif ?>

            <? if ($searchResult['fileRef']->terms_of_use &&
                   $searchResult['fileRef']->terms_of_use->download_condition > 0) : ?>
                <?= Icon::create('lock-locked', Icon::ROLE_INFO)->asImg([
                    'class' => 'text-top',
                    'title' => _('Das Herunterladen dieser Datei ist nur eingeschränkt möglich.')]) ?>
            <? endif; ?>
        <? endif ?>
    </td>

    <td>
        <? if (mb_strlen($searchResult['fileRef']->description)) : ?>
            <? if ($searchResult['folder']->isFileDownloadable($searchResult['fileRef'], $user->id)) : ?>
                <a href="<?= htmlReady($controller->url_for('file/details/'.$searchResult['fileRef']->id)) ?>" data-dialog="">
                    <?= $controller->markPhrase($searchResult['fileRef']->description, $query->getQuery(), 100) ?>
                </a>
            <? else : ?>
                <?= $controller->markPhrase($searchResult['fileRef']->description, $query->getQuery(), 100) ?>
            <? endif ?>
        <? endif ?>
    </td>

    <td>
        <a href="<?= htmlReady(\FilesController::getRangeLink($searchResult['folder'])) ?>">
            <? if ($searchResult['folder']->range_type === 'course') : ?>
                <?= htmlReady($searchResult['folder']->course->getSemType()['name']) ?>:
                <?= $controller->markPhrase($searchResult['folder']->course->name, $query->getQuery()) ?>
            <? elseif ($searchResult['folder']->range_type === 'institute') : ?>
                <?= _('Einrichtung') ?>:
                <?= $controller->markPhrase($searchResult['folder']->institute->name, $query->getQuery()) ?>
            <? elseif ($searchResult['folder']->range_type === 'message') : ?>
                <?= _('Nachrichtenanhang') ?>:
                <?= $controller->markPhrase($searchResult['folder']->message->subject, $query->getQuery()) ?>
            <? elseif ($searchResult['folder']->range_type === 'user') : ?>
                <?= _('Öffentlicher Dateibereich') ?>:
                <?= $controller->markPhrase($searchResult['folder']->user->getFullname('full_rev_username'), $query->getQuery()) ?>
            <? endif ?>
        </a>
    </td>

    <td>
        <? if ($searchResult['fileRef']->owner): ?>
            <span class="files-search-owner">
                <? if ($searchResult['fileRef']->user_id !== $user->id
                       && $searchResult['fileRef']->owner) : ?>
                    <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $searchResult['fileRef']->owner->username) ?>">
                        <?= $controller->markPhrase($searchResult['fileRef']->author_name, $query->getQuery()) ?>
                    </a>
                <? else: ?>
                    <?= $controller->markPhrase($searchResult['fileRef']->author_name, $query->getQuery()) ?>
                <? endif ?>
            </span>
        <? endif ?>
    </td>

    <td>
        <?= $this->render_partial('files_dashboard/_search_time') ?>
    </td>

    <td class="files-search-actions">
        <?= $controller->getActionMenu($searchResult['fileRef'], $searchResult['folder'], $user) ?>
    </td>
</tr>
