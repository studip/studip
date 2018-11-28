<? use \Studip\Button; ?>
<a name="autoren"></a>


<form action="<?= $controller->url_for('course/members/edit_autor') ?>" method="post" data-dialog="">
    <?= CSRFProtection::tokenTag() ?>
    <table id="autor" class="default collapsable tablesorter">
        <caption>
        <? if ($is_tutor) : ?>
            <span class="actions">
                <a href="<?= URLHelper::getLink('dispatch.php/messages/write', [
                    'filter'          => 'send_sms_to_all',
                    'emailrequest'    => 1,
                    'who'             => 'autor',
                    'course_id'       => $course_id,
                    'default_subject' => $subject,
                ]) ?>" data-dialog>
                    <?= Icon::create('inbox', 'clickable', [
                        'title' => sprintf(
                            _('Nachricht mit Mailweiterleitung an alle %s versenden'),
                            $status_groups['autor']
                        )
                    ]) ?>
                </a>
           </span>
       <? endif ?>
            <?= $status_groups['autor'] ?>
        </caption>
        <colgroup>
            <col width="20">
        <? if ($is_tutor) : ?>
            <? if (!$is_locked) : ?>
                <col width="20">
            <? endif ?>
                <col>
                <col width="15%">
                <col width="35%">
                <? $cols = $cols_foot = 6; ?>
        <? else : ?>
                <col>
                <? $cols = 3 ?>
        <? endif ?>
            <col width="80">
        </colgroup>
        <thead>
            <tr class="sortable">
            <? if ($is_tutor && !$is_locked) : ?>
                <th>
                    <input aria-label="<?= sprintf(_('Alle %s auswählen'), $status_groups['autor']) ?>"
                           type="checkbox" name="all" value="1" data-proxyfor=":checkbox[name^=autor]">
                </th>
            <? endif ?>
                <th></th>
                <th <? if ($sort_by === 'nachname' && $sort_status === 'autor') printf('class="sort%s"', $order); ?>>
                    <? $order = $sort_status !== 'autor' ? 'desc' : $order; ?>
                    <a href="<?= URLHelper::getLink(sprintf(
                        '?sortby=nachname&sort_status=autor&order=%s&toggle=%s#autoren',
                        $order,
                        $sort_by === 'nachname'
                    )) ?>">
                       <?= _('Nachname, Vorname') ?>
                   </a>
                </th>
            <? if($is_tutor) :?>
                <th <? if ($sort_by === 'mkdate' && $sort_status === 'autor') printf('class="sort%s"', $order); ?>>
                    <a href="<?= URLHelper::getLink(sprintf(
                        '?sortby=mkdate&sort_status=autor&order=%s&toggle=%s#autoren',
                       $order,
                       $sort_by === 'mkdate'
                    )) ?>">
                        <?= _('Anmeldedatum') ?>
                    </a>
                </th>
                <th><?= _('Studiengang') ?></th>
            <? endif ?>
                <th class="actions"><?= _('Aktion') ?></th>
            </tr>
        </thead>
        <tbody>
        <? $nr = $autor_nr?>
        <? foreach ($autoren as $autor) : ?>
            <? $fullname = $autor['fullname']?>
            <tr>
            <? if ($is_tutor && !$is_locked) : ?>
                <td>
                    <input aria-label="<?= sprintf(_('%s auswählen'), $status_groups['autor']) ?>"
                           type="checkbox" name="autor[<?= $autor['user_id'] ?>]" value="1"
                           <? if (isset($flash['checked']) && in_array($autor['user_id'], $flash['checked'])) echo 'checked'; ?>>
                </td>
            <? endif ?>
                <td style="text-align: right"><?= sprintf('%02u', ++$nr) ?></td>
                <td>
                    <a href="<?= $controller->url_for('profile?username=' . $autor['username']) ?>" <? if ($autor['mkdate'] >= $last_visitdate) echo 'class="new-member"'; ?>>
                        <?= Avatar::getAvatar($autor['user_id'], $autor['username'])->getImageTag(Avatar::SMALL, [
                            'style' => 'margin-right: 5px',
                            'title' => htmlReady($fullname),
                        ]) ?>
                        <?= htmlReady($fullname) ?>
                    <? if ($user_id === $autor['user_id'] && $autor['visible'] === 'no') : ?>
                       (<?= _('unsichtbar') ?>)
                   <? endif ?>
                    </a>
                <? if ($is_tutor && $autor['comment']) : ?>
                    <?= tooltipHtmlIcon(sprintf(
                        '<strong>%s</strong><br>%s',
                        _('Bemerkung'),
                        htmlReady($autor['comment'])
                    )) ?>
                <? endif ?>
                </td>
            <? if ($is_tutor) : ?>
                <td>
                <? if (!empty($autor['mkdate'])) : ?>
                    <?= strftime('%x %X', $autor['mkdate'])?>
                <? endif ?>
                </td>
                <td>
                    <?= $this->render_partial('course/members/_studycourse.php', [
                        'studycourses' => new SimpleCollection(UserStudyCourse::findByUser($autor['user_id'])),
                    ]) ?>
                </td>
            <? endif ?>

                <td class="actions">
                    <? $actionMenu = ActionMenu::get() ?>
                    <? if ($is_tutor) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('course/members/add_comment/' . $autor['user_id']),
                            _('Bemerkung hinzufügen'),
                            Icon::create('comment', 'clickable'),
                            ['data-dialog' => 'size=auto']
                        ) ?>
                    <? endif ?>
                    <? if ($user_id !== $autor['user_id']) : ?>
                        <? $actionMenu->addLink(
                            URLHelper::getLink('dispatch.php/messages/write', [
                                'filter'           => 'send_sms_to_all',
                                'emailrequest'    => 1,
                                'rec_uname'       => $autor['username'],
                                'default_subject' => $subject,
                            ]),
                            _('Nachricht mit Mailweiterleitung senden'),
                            Icon::create('mail', 'clickable', [
                                'title' => sprintf(_('Nachricht mit Weiterleitung an %s senden'), $fullname),
                            ]),
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
                    <? if ($is_tutor && !$is_locked) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('course/members/cancel_subscription/singleuser/autor/' . $autor['user_id']),
                            _('Aus Veranstaltung austragen'),
                            Icon::create('door-leave', 'clickable', [
                                'title' => sprintf(_('%s austragen'), $fullname)
                            ]
                        )) ?>
                    <? endif ?>
                    <?= $actionMenu->render() ?>
                </td>
            </tr>
        <? endforeach ?>
        <? if ($invisibles > 0) : ?>
            <tr>
                <td colspan="<?= $cols ?>" class="blank"></td>
            </tr>
            <tr>
                <td colspan="<?= $cols ?>">+ <?= sprintf(_('%u unsichtbare %s'), $invisibles, $status_groups['autor']) ?></td>
            </tr>
        <? endif ?>

        </tbody>
    <? if ($is_tutor && !$is_locked && count($autoren) > 0) : ?>
        <tfoot>
            <tr>
                <td colspan="<?= $cols_foot ?>">
                    <select name="action_autor" id="action_autor" aria-label="<?= _('Aktion ausführen') ?>">
                        <option value="">- <?= _('Aktion wählen') ?></option>
                    <? if($is_dozent) : ?>
                        <option value="upgrade">
                            <?= sprintf(_('Zu %s hochstufen'), htmlReady($status_groups['tutor'])) ?>
                        </option>
                    <? endif ?>
                        <option value="downgrade">
                            <?= sprintf(_('Zu %s herunterstufen'), htmlReady($status_groups['user'])) ?>
                        </option>
                    <? if ($to_waitlist_actions) : ?>
                        <option value="to_admission_first">
                            <?= _('An den Anfang der Warteliste verschieben') ?>
                        </option>
                        <option value="to_admission_last">
                            <?= _('Ans Ende der Warteliste verschieben') ?>
                        </option>
                    <? endif ?>
                        <option value="remove"><?= _('Austragen') ?></option>
                    <? if($is_dozent) : ?>
                        <option value="to_course">
                            <?= _('In andere Veranstaltung verschieben/kopieren') ?>
                        </option>
                    <? endif ?>
                        <option value="message"><?=_('Nachricht senden')?></option>
                    </select>
                    <?= Button::create(_('Ausführen'), 'submit_autor') ?>
                </td>
            </tr>
        </tfoot>
    <? endif ?>
    </table>
</form>
