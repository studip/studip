<? if ($GLOBALS['perm']->have_studip_perm('tutor', $sem_id)): ?>
<form action="<?= $controller->edit_members('bulk', $type) ?>" method="post">
<? endif; ?>
    <table class="default studygroupmemberlist sortable-table" id="studygroup-members-<?= $type ?>">
        <colgroup>
        <? if ($GLOBALS['perm']->have_studip_perm('tutor', $sem_id)): ?>
            <col style="width: 24px">
        <? endif; ?>
            <col style="width: 40px">
            <col>
            <col style="width: 80px">
        </colgroup>
        <caption>
            <?= $title ?>
        </caption>
        <thead>
            <tr>
            <? if ($GLOBALS['perm']->have_studip_perm('tutor', $sem_id)): ?>
                <th>
                    <input type="checkbox"
                           data-proxyfor="#studygroup-members-<?= $type ?> tbody :checkbox"
                           data-activates="#studygroup-members-<?= $type ?> tfoot .button">
                </th>
            <? endif; ?>
                <th data-sort="false"></th>
                <th data-sort="text"><?= _('Name') ?></th>
                <th data-sort="false" class="actions"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($members as $m): ?>
            <? $fullname = $m instanceof CourseMember ? $m->user->getFullname('no_title_rev') : $m['fullname']?>
            <tr <? if ($last_visitdate <= $m['mkdate'] && $GLOBALS['perm']->have_studip_perm('tutor', $sem_id)) echo 'class="new-member"'; ?>>
            <? if ($GLOBALS['perm']->have_studip_perm('tutor', $sem_id)): ?>
                <td>
                    <input type="checkbox" name="members[]" value="<?= htmlReady($m['username']) ?>">
                </td>
            <? endif; ?>
                <td>
                    <a class="member-avatar"
                       href="<?= $controller->link_for('profile', ['username' => $m['username']]) ?>">
                        <?= Avatar::getAvatar($m['user_id'])
                                  ->getImageTag(Avatar::SMALL, ['title' => $fullname]) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= $controller->link_for('profile', ['username' => $m['username']]) ?>">
                        <?= htmlReady($fullname) ?>
                    </a>
                </td>
                <td class="actions">
                    <a href="<?= $controller->link_for('messages/write', ['rec_uname' => $m['username']]) ?>"
                       data-dialog="size=50%">
                        <?= Icon::create('mail')->asImg(['title' => _('Nachricht schreiben')]) ?>
                    </a>
                <? if ($GLOBALS['perm']->have_studip_perm('tutor', $sem_id)) : ?>
                    <?= $this->render_partial('course/studygroup/_members_options.php', compact('m')) ?>
                <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    <? if ($GLOBALS['perm']->have_studip_perm('tutor', $sem_id)): ?>
        <tfoot>
            <tr>
                <td colspan="4">
                    <?= Studip\Button::create(_('Nachricht schreiben'), 'mail', [
                        'data-dialog' => 'size=50%',
                    ]) ?>
                <? if ($GLOBALS['perm']->have_studip_perm('dozent', $sem_id) && in_array($type, ['autor', 'tutor'])): ?>
                    <?= Studip\Button::create(_('Hochstufen'), 'promote', [
                        'data-confirm' => _('Wollen Sie die markierten Personen wirklich hochstufen?'),
                    ]) ?>
                <? endif; ?>
                <? if ($GLOBALS['perm']->have_studip_perm('dozent', $sem_id) && in_array($type, ['tutor', 'moderator'])): ?>
                    <?= Studip\Button::create(_('Herunterstufen'), 'downgrade', [
                        'data-confirm' => _('Wollen Sie die markierten Personen wirklich herunterstufen?'),
                        ]) ?>
                <? endif; ?>
                <? if ($type !== 'moderator'): ?>
                    <?= Studip\Button::create(_('Austragen'), 'remove', [
                        'data-confirm' => _('Wollen Sie die markierten Personen wirklich auf der Studiengruppe entfernen?'),
                    ]) ?>
                <? endif; ?>
                </td>
            </tr>
        </tfoot>
    <? endif; ?>
    </table>
<? if ($GLOBALS['perm']->have_studip_perm('tutor', $sem_id)): ?>
</form>
<? endif; ?>
