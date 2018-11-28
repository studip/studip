
<table class="default collapsable ">
    <caption>
    <? if ($is_tutor) : ?>
        <span class="actions">
            <a href="<?= URLHelper::getLink('dispatch.php/messages/write', [
                'filter'          => 'send_sms_to_all',
                'emailrequest'    => 1,
                'who'             => 'dozent',
                'course_id'       => $course_id,
                'default_subject' => $subject
            ]) ?>" data-dialog>
                <?= Icon::create('inbox', 'clickable', ['title' => sprintf(_('Nachricht mit Mailweiterleitung an alle %s versenden'), $status_groups['dozent'])])->asImg() ?>
            </a>
        </span>
    <? endif ?>
        <?= $this->status_groups['dozent'] ?>
    </caption>
    <colgroup>
        <col width="<?= $is_tutor ? 40 : 20 ?>">
        <col>
        <col width="80">
    </colgroup>
    <thead>
        <tr class="sortable">
            <th></th>
            <th <? if ($sort_by === 'nachname' && $sort_status === 'dozent') printf('class="sort%s"', $order); ?>>
                <? $order = $sort_status !== 'dozent' ? 'desc' : $order; ?>
                <a href="<?= URLHelper::getLink(sprintf(
                    '?sortby=nachname&sort_status=dozent&order=%s&toggle=%s',
                    $order, $sort_by === 'nachname'
                )) ?>">
                    <?= _('Nachname, Vorname') ?>
                </a>
            </th>
            <th class="actions"><?= _('Aktion') ?></th>
        </tr>
    </thead>
    <tbody>
    <? $nr = 0?>
    <? foreach($dozenten as $dozent) : ?>
        <? $fullname = $dozent['fullname'];?>
        <tr>
            <td style="text-align: right"><?= (++$nr < 10) ? sprintf('%02d', $nr) : $nr ?></td>
            <td>
                <a href="<?= $controller->url_for(sprintf('profile?username=%s',$dozent['username'])) ?>" <? if ($dozent['mkdate'] >= $last_visitdate) echo 'class="new-member"'; ?>>
                    <?= Avatar::getAvatar($dozent['user_id'], $dozent['username'])->getImageTag(Avatar::SMALL, [
                        'style' => 'margin-right: 5px',
                        'title' => htmlReady($fullname),
                    ]) ?>
                    <?= htmlReady($fullname) ?>
                </a>
            <? if ($is_tutor && $dozent['comment']) : ?>
                <?= tooltipHtmlIcon(sprintf(
                    '<strong>%s</strong><br>%s',
                    _('Bemerkung'),
                    htmlReady($dozent['comment'])
                )) ?>
            <? endif ?>
            </td>
            <td class="actions">
                <? $actionMenu = ActionMenu::get() ?>
                <? if ($is_tutor) : ?>
                    <? $actionMenu->addLink(
                        $controller->url_for('course/members/add_comment/' . $dozent['user_id']),
                        _('Bemerkung hinzufügen'),
                        Icon::create('comment', 'clickable'),
                        ['data-dialog' => 'size=auto']
                    ) ?>
                <? endif ?>
                <? if($user_id !== $dozent['user_id']) : ?>
                    <? $actionMenu->addLink(
                        URLHelper::getLink('dispatch.php/messages/write',[
                            'filter'           => 'send_sms_to_all',
                            'emailrequest'    => 1,
                            'rec_uname'       => $dozent['username'],
                            'default_subject' => $subject,
                        ]),
                        _('Nachricht mit Mailweiterleitung senden'),
                        Icon::create('mail', 'clickable', ['title' => sprintf('Nachricht mit Weiterleitung an %s senden', $fullname)]),
                        ['data-dialog' => '1']
                    ) ?>
                <? else: ?>
                    <? $actionMenu->addLink(
                        '#',
                        _('Nachricht mit Mailweiterleitung senden'),
                        Icon::create('mail', Icon::ROLE_INACTIVE),
                        ['disabled' => true]
                    ) ?>
                <? endif ?>
                <? if (!$dozent_is_locked && $is_dozent && $user_id !== $dozent['user_id'] && count($dozenten) > 1) : ?>
                    <? $actionMenu->addLink(
                        $controller->url_for('course/members/cancel_subscription/singleuser/dozent/' . $dozent['user_id']),
                        _('Aus Veranstaltung austragen'),
                        Icon::create('door-leave', 'clickable', ['title' => sprintf(_('%s austragen'), $fullname)])
                    ) ?>
                <? endif ?>
                <?= $actionMenu->render() ?>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
