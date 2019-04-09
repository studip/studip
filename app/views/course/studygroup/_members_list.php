<table class="default studygroupmemberlist sortable-table">
    <colgroup>
        <col width="40">
        <col>
        <col width="48">
    </colgroup>
    <caption>
        <?= $title ?>
    </caption>
    <thead>
        <tr>
            <th data-sort="false"></th>
            <th data-sort="text"><?= _('Name') ?></th>
            <th data-sort="false" class="actions"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($members as $m): ?>
        <? $fullname = $m instanceof CourseMember ? $m->user->getFullname('no_title_rev') : $m['fullname']?>
        <tr <? if ($last_visitdate <= $m['mkdate'] && $GLOBALS['perm']->have_studip_perm('tutor', $sem_id)) echo 'class="new-member"'; ?>>
            <td>
                <a class="member-avatar"
                   href="<?= $controller->url_for('profile', ['username' => $m['username']]) ?>">
                    <?= Avatar::getAvatar($m['user_id'])
                              ->getImageTag(Avatar::SMALL, tooltip2($fullname)) ?>
                </a>
            </td>
            <td>
                <a href="<?= $controller->url_for('profile', ['username' => $m['username']]) ?>">
                    <?= htmlReady($fullname) ?>
                </a>
            </td>
            <td class="actions">
                <a href="<?= $controller->url_for('messages/write', ['rec_uname' => $m['username']]) ?>"
                   data-dialog="size=50%">
                    <?= Icon::create('mail', 'clickable', ['title' => _('Nachricht schreiben')])->asImg(20) ?>
                </a>
                <? if ($GLOBALS['perm']->have_studip_perm('tutor', $sem_id) || $GLOBALS['perm']->have_studip_perm('admin', $sem_id)) : ?>
                    <?= $this->render_partial('course/studygroup/_members_options.php', compact('m')) ?>
                <? endif ?>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
