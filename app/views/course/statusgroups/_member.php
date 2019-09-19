<? $user_fullname = $m->getUserFullname('full_rev') ?>
<tr>
<? if ($is_tutor && !$is_locked) : ?>
    <td>
        <input aria-label="<?= _('Mitglieder auswählen') ?>"
               type="checkbox" name="group[<?= $group->id ?>][<?= $m->user_id ?>]"
               class="groupmembers-<?= $group->id ?>" value="1"
               data-activates="select#members-action-<?= $group->id ?>">
    </td>
<? endif ?>
    <td>
        <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>
    </td>
    <td>
        <a href="<?= $controller->url_for(sprintf('profile?username=%s', $m->username)) ?>">
            <?= Avatar::getAvatar($m->user_id, $m->username)->getImageTag(Avatar::SMALL, [
                'style' => 'margin-right: 5px',
                'title' => $user_fullname,
            ]) ?>
            <?= htmlReady($user_fullname) ?>
            <?php if ($user_id == $m->user_id && $m->visible == 'no') : ?>
                (<?= _('unsichtbar') ?>)
            <?php endif ?>
        </a>
    </td>
<? if ($is_tutor) : ?>
    <? if ($group->id !== 'nogroup'): ?>
        <td>
            <?= $m->mkdate ? strftime('%x %X', $m->mkdate) : '' ?>
        </td>
    <? endif; ?>
    <td>
    <?= $this->render_partial('course/members/_studycourse.php',
                ['studycourses' => new SimpleCollection(UserStudyCourse::findByUser($m->user_id))]) ?>
    </td>
<? endif ?>
    <td class="memberactions">
        <? $actions = ActionMenu::get();
           if ($is_tutor || $m->user_id !== $GLOBALS['user']->id) {
               $actions->addLink(
            $controller->url_for('messages/write', [
                'rec_uname'       => $m->username,
                'default_subject' => $course_title,
            ]),
            _('Nachricht schicken'),
            Icon::create('mail', 'clickable', [
                'title' => sprintf(_('Nachricht an %s schicken'), $user_fullname),
            ]),
            ['data-dialog' => 'size=auto']
                );
           }
           if ($is_tutor) {
                $actions->addLink(
            $controller->url_for('course/statusgroups/move_member', $m->user_id, $group->id),
            _('In eine andere Gruppe verschieben'),
            Icon::create('person+move_right', 'clickable', [
                'title' => sprintf(
                    _('%s in eine andere Gruppe verschieben'),
                            $user_fullname
                ),
            ]),
            ['data-dialog' => 'size=auto']
                );
           }
           if ($group->id !== 'nogroup' && ($is_tutor || ($m->user_id === $GLOBALS['user']->id && $group->userMayLeave($GLOBALS['user']->id)))) {
                $actions->addLink(
            $controller->url_for('course/statusgroups/delete_member', $m->user_id, $group->id),
            _('Aus der Gruppe entfernen'),
            Icon::create('trash', 'clickable', [
                'title' => sprintf(
                    _('%s aus Gruppe %s entfernen'),
                    $user_fullname,
                    $group->name
                ),
            ]),
            ['data-confirm' => sprintf(
                _('Soll %s wirklich aus der Gruppe %s entfernt werden?'),
                $user_fullname,
                $group->name
                )]
                );
           }
           echo $actions;
           ?>
    </td>
</tr>
