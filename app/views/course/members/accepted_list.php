<? use \Studip\Button; ?>
<br />
<a name="users"></a>

<form action="<?= $controller->url_for('course/members/edit_accepted/') ?>" method="post" data-dialog="size=50%">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default collapsable">
        <caption>
            <span class="actions">
                <a href="<?= URLHelper::getLink('dispatch.php/messages/write', [
                    'filter'           => 'prelim',
                    'emailrequest'     => 1,
                    'course_id'        => $course_id,
                    'default_subject'  => $subject,
                ]) ?>" data-dialog>
                    <?= Icon::create('inbox', 'clickable', ['title' => sprintf(_('Nachricht mit Mailweiterleitung an alle %s versenden'),'vorläufig akzeptierten Nutzer/-innen')]) ?>
                </a>
            </span>
            <?= _('Vorläufig akzeptierte Teilnehmende') ?>
        </caption>
        <colgroup>
        <? if (!$is_locked): ?>
            <col width="20">
        <? endif ?>
            <col width="20">
            <col>
            <col width="15%">
            <col width="35%">
            <col width="80">
        </colgroup>
        <thead>
            <tr class="sortable">
            <? if (!$is_locked): ?>
                <th>
                    <input aria-label="<?= sprintf(_('Alle %s auswählen'), 'vorläufig akzeptierten NutzerInnen') ?>"
                               type="checkbox" name="all" value="1" data-proxyfor=":checkbox[name^=accepted]">
                </th>
            <? endif ?>
                <th></th>
                <th <?if ($sort_by === 'nachname' && $sort_status === 'accepted') printf('class="sort%s"', $order); ?>>
                    <? $order = $sort_status !== 'accepted' ? 'desc' : $order; ?>
                    <a href="<?= URLHelper::getLink(sprintf(
                        '?sortby=nachname&sort_status=accepted&order=%s&toggle=%s#users',
                        $order,
                        $sort_by === 'nachname'
                    )) ?>">
                        <?= _('Nachname, Vorname') ?>
                    </a>
                </th>
                <th <? if ($sort_by === 'mkdate' && $sort_status === 'accepted') printf('class="sort%s"', $order); ?>>
                    <a href="<?= URLHelper::getLink(sprintf(
                        '?sortby=mkdate&sort_status=accepted&order=%s&toggle=%s#accepted',
                       $order,
                       $sort_by === 'mkdate'
                    )) ?>">
                        <?= _('Anmeldedatum') ?>
                    </a>
                </th>
                <th><?=_('Studiengang')?></th>
                <th class="actions"><?= _('Aktion') ?></th>
            </tr>
        </thead>
        <tbody>
        <? $nr= 0; ?>
        <? foreach ($accepted as $accept) : ?>
        <? $fullname = $accept['fullname'];?>
            <tr>
            <? if (!$is_locked) : ?>
                <td>
                    <input aria-label="<?= sprintf(_('%s auswählen'), _('Vorläufig akzeptierte/n NutzerIn')) ?>"
                           type="checkbox" name="accepted[<?= $accept['user_id'] ?>]" value="1"
                           <? if (isset($flash['checked']) && in_array($accept['user_id'], $flash['checked'])) echo 'checked'; ?>>
                </td>
            <? endif ?>
                <td style="text-align: right"><?= sprintf('%02u', ++$nr) ?></td>
                <td>
                    <a href="<?= $controller->url_for('profile?username=' . $accept['username']) ?>" <? if ($accept['mkdate'] >= $last_visitdate) echo 'class="new-member"'; ?>>
                        <?= Avatar::getAvatar($accept['user_id'], $accept['username'])->getImageTag(Avatar::SMALL, [
                            'style' => 'margin-right: 5px',
                             'title' => htmlReady($fullname)
                        ]) ?>
                        <?= htmlReady($fullname) ?>
                    </a>
                <? if ($accept['comment']): ?>
                    <?= tooltipHtmlIcon(sprintf(
                        '<strong>%s</strong><br>%s',
                        _('Bemerkung'),
                        htmlReady($accept['comment'])
                    )) ?>
                <? endif ?>
                </td>
                <td>
                <? if (!empty($accept['mkdate'])) : ?>
                    <?= strftime('%x %X', $accept['mkdate'])?>
                <? endif ?>
                </td>
                <td>
                    <?= $this->render_partial('course/members/_studycourse.php', [
                        'studycourses' => new SimpleCollection(UserStudyCourse::findByUser($accept['user_id']))
                    ]) ?>
                </td>
                <td class="actions">
                    <? $actionMenu = ActionMenu::get() ?>
                    <? $actionMenu->addLink(
                        $controller->url_for('course/members/add_comment/' . $accept['user_id']),
                        _('Bemerkung hinzufügen'),
                        Icon::create('comment', 'clickable'),
                        ['data-dialog' => 'size=auto']
                    ) ?>
                    <? if ($user_id !== $accept['user_id']) : ?>
                        <? $actionMenu->addLink(
                            URLHelper::getLink('dispatch.php/messages/write',[
                                'filter'           => 'send_sms_to_all',
                                'emailrequest'    => 1,
                                'rec_uname'       => $accept['username'],
                                'default_subject' => $subject,
                            ]),
                            _('Nachricht mit Mailweiterleitung senden'),
                            Icon::create('mail', 'clickable', ['title' => sprintf('Nachricht mit Weiterleitung an %s senden', $fullname)]),
                            ['data-dialog' => '']
                        ) ?>
                    <? endif?>
                    <? if (!$is_locked) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('course/members/cancel_subscription/singleuser/accepted/' . $accept['user_id']),
                            _('Aus Veranstaltung austragen'),
                            Icon::create('door-leave', 'clickable', [
                                'title' => sprintf(_('%s austragen'), htmlReady($fullname))
                            ])
                        ) ?>
                    <? endif ?>
                    <?= $actionMenu->render() ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    <? if (!$is_locked) : ?>
        <tfoot>
            <tr>
                <td class="printhead" colspan="6">
                    <select name="action_accepted" id="action_accepted" aria-label="<?= _('Aktion ausführen') ?>">
                        <option value="">- <?= _('Aktion wählen') ?></option>
                        <option value="upgrade"><?= _('Akzeptieren') ?></option>
                        <option value="remove"><?= _('Austragen') ?></option>
                        <option value="message"><?=_('Nachricht senden')?></option>
                        <!--<option value="copy_to_course"><?= _('In Seminar verschieben/kopieren') ?></option>-->
                    </select>
                    <?= Button::create(_('Ausführen'), 'submit_accepted') ?>
                </td>
            </tr>
        </tfoot>
    <? endif ?>
    </table>
</form>
