<? use \Studip\Button; ?>

<a name="tutoren"></a>
<form action="<?= $controller->url_for('course/members/edit_tutor') ?>" method="post" data-dialog="size=50%">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default collapsable">
        <caption>
        <? if($is_tutor) : ?>
            <span class="actions">
                <a href="<?= URLHelper::getLink('dispatch.php/messages/write', [
                    'filter'          => 'send_sms_to_all',
                    'who'             => 'tutor',
                    'emailrequest'    => 1,
                    'course_id'       => $course_id,
                    'default_subject' => $subject,
                ]) ?>" data-dialog>
                    <?= Icon::create('inbox', 'clickable', [
                        'title' => sprintf(_('Nachricht mit Mailweiterleitung an alle %s versenden'), $status_groups['tutor'])
                    ]) ?>
                </a>
            </span>
        <? endif ?>
            <?= $status_groups['tutor'] ?>
        </caption>
        <colgroup>
        <? if ($is_dozent && !$tutor_is_locked) : ?>
            <col width="20">
        <? endif ?>
            <col width="<?= (($is_tutor && !$is_dozent) || $tutor_is_locked) ? 40 : 20 ?>">
            <col>
        <? if ($is_dozent) : ?>
            <col width="15%">
            <col width="35%">
        <? endif ?>
            <col width="80">
        </colgroup>
        <thead>
            <tr class="sortable">
            <? if ($is_dozent && !$tutor_is_locked): ?>
                <th>
                    <input aria-label="<?= sprintf(_('Alle %s auswählen'), $status_groups['tutor']) ?>"
                           type="checkbox" name="all" value="1" data-proxyfor=":checkbox[name^=tutor]">
                </th>
            <? endif ?>
                <th></th>
                <th <? if ($sort_by === 'nachname' && $sort_status === 'tutor') printf('class="sort%s"', $order); ?>>
                    <? $order = $sort_status !== 'tutor' ? 'desc' : $order; ?>
                    <a href="<?= URLHelper::getLink(sprintf(
                        '?sortby=nachname&sort_status=tutor&order=%s&toggle=%s#tutoren',
                        $order,
                        $sort_by === 'nachname'
                    )) ?>">
                        <?= _('Nachname, Vorname') ?>
                    </a>
                </th>
            <? if($is_dozent) : ?>
                <th <? if ($sort_by === 'mkdate' && $sort_status === 'tutor') printf('class="sort%s"', $order); ?>>
                    <a href="<?= URLHelper::getLink(sprintf(
                        '?sortby=mkdate&sort_status=tutor&order=%s&toggle=%s#tutoren',
                       $order,
                       $sort_by === 'mkdate'
                    )) ?>">
                        <?= _('Anmeldedatum') ?>
                    </a>
                </th>
                <th><?=_('Studiengang')?></th>
            <? endif ?>
                <th class="actions"><?= _('Aktion') ?></th>
            </tr>
        </thead>
        <tbody>
        <? $nr= 0; ?>
        <? foreach ($tutoren as $tutor) : ?>
            <? $fullname = $tutor['fullname'];?>
            <tr>
            <? if ($is_dozent && !$tutor_is_locked): ?>
                <td>
                    <input aria-label="<?= sprintf(_('%s auswählen'), $status_groups['tutor']) ?>"
                           type="checkbox" name="tutor[<?= $tutor['user_id'] ?>]" value="1"
                           <? if (isset($flash['checked']) && in_array($tutor['user_id'], $flash['checked'])) echo 'checked'; ?>>
                </td>
            <? endif ?>
                <td style="text-align: right"><?= sprintf('%02u', ++$nr) ?></td>
                <td>
                    <a href="<?= $controller->url_for('profile?username=' . $tutor['username']) ?>" <? if ($tutor['mkdate'] >= $last_visitdate) echo 'class="new-member"'; ?>>
                        <?= Avatar::getAvatar($tutor['user_id'], $tutor['username'])->getImageTag(Avatar::SMALL, [
                            'style' => 'margin-right: 5px',
                            'title' => htmlReady($fullname)
                        ]) ?>
                        <?= htmlReady($fullname) ?>
                    </a>
                <? if ($is_tutor && $tutor['comment']) : ?>
                    <?= tooltipHtmlIcon(sprintf(
                        '<strong>%s</strong><br>%s',
                        _('Bemerkung'),
                        htmlReady($tutor['comment'])
                    )) ?>
                <? endif ?>
                </td>
            <? if($is_dozent) : ?>
                <td>
                <? if (!empty($tutor['mkdate'])) : ?>
                    <?= strftime('%x %X', $tutor['mkdate'])?>
                <? endif ?>
                </td>
                <td>
                    <?= $this->render_partial('course/members/_studycourse.php', [
                        'studycourses' => new SimpleCollection(UserStudyCourse::findByUser($tutor['user_id']))
                    ]) ?>
                </td>
            <? endif ?>
                <td class="actions">
                    <? $actionMenu = ActionMenu::get() ?>
                    <? if ($is_tutor) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('course/members/add_comment/' . $tutor['user_id']),
                            _('Bemerkung hinzufügen'),
                            Icon::create('comment', 'clickable'),
                            ['data-dialog' => 'size=auto']
                        ) ?>
                    <? endif ?>
                    <? if ($user_id !== $tutor['user_id']) : ?>
                        <? $actionMenu->addLink(
                            URLHelper::getLink('dispatch.php/messages/write', [
                                'filter'           => 'send_sms_to_all',
                                'emailrequest'    => 1,
                                'rec_uname'       => $tutor['username'],
                                'default_subject' => $subject,
                            ]),
                            _('Nachricht mit Mailweiterleitung senden'),
                            Icon::create('mail', 'clickable', ['title' => sprintf(_('Nachricht mit Weiterleitung an %s senden'), $fullname)]),
                            ['data-dialog' => '']
                        ) ?>
                    <? else: ?>
                        <? $actionMenu->addLink(
                            '#',
                            _('Nachricht mit Mailweiterleitung senden'),
                            Icon::create('mail', Icon::ROLE_INACTIVE),
                            ['disabled' => true]
                        ) ?>
                    <? endif ?>
                    <? if ($is_dozent && !$tutor_is_locked && $user_id !== $tutor['user_id'] && count($tutoren) >= 1) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('course/members/cancel_subscription/singleuser/tutor/' . $tutor['user_id']),
                            _('Aus Veranstaltung austragen'),
                            Icon::create('door-leave', 'clickable', ['title' => sprintf(_('%s austragen'),htmlReady($fullname))])
                        ) ?>
                    <? endif ?>
                    <?= $actionMenu->render() ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    <? if ($is_dozent && !$tutor_is_locked): ?>
        <tfoot>
            <tr>
                <td colspan="6">
                    <select name="action_tutor" id="tutor_action" aria-label="<?= _('Aktion ausführen') ?>">
                        <option value="">- <?= _('Aktion auswählen') ?></option>
                        <option value="downgrade"><?= sprintf(_('Zu %s herunterstufen'), $status_groups['autor']) ?></option>
                        <option value="remove"><?= _('Austragen') ?></option>
                        <option value="message"><?=_('Nachricht senden')?></option>
                        <!--<option value="copy_to_course"><?= _('In Seminar verschieben/kopieren') ?></option>-->
                    </select>
                    <?= Button::create(_('Ausführen'), 'submit_autor') ?>
                </td>
            </tr>
        </tfoot>
    <? endif ?>
    </table>
</form>
