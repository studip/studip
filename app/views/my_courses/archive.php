<? if (empty($seminars)): ?>
    <?= MessageBox::info(_('Es befinden sich zur Zeit keine Veranstaltungen im Archiv, an denen Sie teilgenommen haben.')) ?>
<? else: ?>
    <? foreach ($seminars as $semester => $rows): ?>
        <table class="default">
            <? if ($semester): ?>
                <caption><?= htmlReady($semester) ?></caption>

            <? endif; ?>
            <colgroup>
                <col width="80%">
                <col width="10%">
                <col width="10%">
            </colgroup>
            <thead>
            <tr>
                <th>
                    <a href="<?= $controller->url_for('my_courses/archive?sortby=name') ?>">
                        <?= _('Name') ?>
                    </a>
                </th>
                <th style="text-align: center"><?= _('Inhalt') ?></th>
                <th style="text-align: center">
                    <a href="<?= $controller->url_for('my_courses/archive?sortby=status') ?>">
                        <?= _('Status') ?>
                    </a>
                </th>
            </tr>
            </thead>
            <tbody>
            <? foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <a href="<?= URLHelper::getLink('dispatch.php/archive/overview/' . $row['seminar_id']) ?>" data-dialog>
                            <?= htmlReady($row['name']) ?>
                        </a>
                    </td>
                    <td align="center">
                        <? if ($row['forumdump'] and archiv_check_perm($row['seminar_id'])) : ?>
                            <a href="<?= URLHelper::getLink('dispatch.php/archive/forum/' . $row['seminar_id']) ?>" data-dialog>
                                <?= Icon::create('forum', 'clickable', ['title' => _('Beiträge des Forums der Veranstaltung')])->asImg(20) ?>
                            </a>
                        <? else: ?>
                            <?= Icon::create('forum', 'inactive')->asImg(20, ["style" => 'visibility: hidden;']) ?>
                        <? endif; ?>


                        <? $course = ArchivedCourse::find($row['seminar_id']); ?>

                        <? if(($row['archiv_file_id']) and archiv_check_perm($row['seminar_id'])): ?>
                            <a href="<?= FileManager::getDownloadLinkForArchivedCourse($course, false) ?>">
                                <?= Icon::create('file-archive', 'clickable', ['title' => _('Dateisammlung der Veranstaltung herunterladen')])->asImg(20) ?>
                            </a>
                        <? elseif(($row['archiv_protected_file_id']) and archiv_check_perm($row['seminar_id'] == 'admin')): ?>
                            <a href="<?= FileManager::getDownloadLinkForArchivedCourse($course, true) ?>">
                                <?= Icon::create('file-archive', 'clickable', ['title' => _('Dateisammlung der Veranstaltung herunterladen')])->asImg(20) ?>
                            </a>
                        <? else: ?>
                            <?= Icon::create('file-archive', 'inactive')->asImg(20, ["style" => 'visibility: hidden;']) ?>
                        <? endif; ?>

                        <? if ($row['wikidump'] and archiv_check_perm($row['seminar_id'])) : ?>
                            <a href="<?= URLHelper::getLink('dispatch.php/archive/wiki/' . $row['seminar_id']) ?>" data-dialog>
                                <?= Icon::create('wiki', 'clickable', ['title' => _('Beiträge des Wikis der Veranstaltung')])->asImg(20) ?>
                            </a>
                        <? else: ?>
                            <?= Icon::create('wiki', 'inactive')->asImg(20, ["style" => 'visibility: hidden;']) ?>
                        <? endif; ?>
                    </td>
                    <td style="text-align: center"><?= $row['status'] ?></td>
                </tr>
            <? endforeach; ?>
            </tbody>
        </table>
    <? endforeach; ?>
<? endif; ?>
