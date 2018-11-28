<?php
echo $flash['message'];
?>

<? if (count($room_requests)) : ?>
    <table class="default">
        <caption>
            <?= _("Vorhandene Raumanfragen") ?>
        </caption>
        <colgroup>
            <col width="50%">
            <col width="15%">
            <col width="25">
            <col>
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Art der Anfrage') ?></th>
                <th><?= _('Anfragender') ?></th>
                <th><?= _('Bearbeitungsstatus') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($room_requests as $rr): ?>
            <tr>
                <td>
                    <?= htmlReady($rr->getTypeExplained(), 1, 1) ?>
                </td>
                <td>
                    <?= htmlReady($rr['user_id'] ? get_fullname($rr['user_id']) : '') ?>
                </td>
                <td>
                    <?= htmlReady($rr->getStatusExplained()) ?>
                </td>
                <td class="actions">
                    <a class="load-in-new-row"
                       href="<?= $controller->link_for('info/' . $rr->getId()) ?>">
                        <?= Icon::create('info', 'clickable', ['title' => _('Weitere Informationen einblenden')])->asImg(16) ?>
                    </a>
                    <? $params = ['request_id' => $rr->getId()] ?>
                    <? $dialog = []; ?>
                    <? if (Request::isXhr()) : ?>
                        <? $params['asDialog'] = true; ?>
                        <? $dialog['data-dialog'] = 'size=big' ?>
                    <? endif ?>

                    <? $actionMenu = ActionMenu::get() ?>
                    <? $actionMenu->addLink(
                        $controller->link_for('edit/' . $course_id, $params),
                        _('Diese Anfrage bearbeiten'),
                        Icon::create('edit', 'clickable', ['title' => _('Diese Anfrage bearbeiten')]),
                        $dialog
                    ) ?>

                    <? if (getGlobalPerms($GLOBALS['user']->id) === 'admin' || ($GLOBALS['perm']->have_perm('admin') && count(getMyRoomRequests(null, null, true, $rr->getId())))) : ?>
                        <? $actionMenu->addLink(
                            URLHelper::getLink('resources.php', ['view' => 'edit_request', 'single_request' => $rr->getId()]),
                            _('Diese Anfrage selbst auflösen'),
                            Icon::create('admin', 'clickable', ['title' => _('Diese Anfrage selbst auflösen')])
                        ) ?>
                    <? endif ?>

                    <? $actionMenu->addLink(
                        $controller->link_for('delete/' . $course_id, ['request_id' => $rr->getId()]),
                        _('Diese Anfrage zurückziehen'),
                        Icon::create('trash', 'clickable', ['title' => _('Diese Anfrage zurückziehen')])
                    ); ?>
                    <?= $actionMenu->render() ?>
                </td>
            </tr>
        <? endforeach ?>
        <? if ($request_id == $rr->getId()) : ?>
            <tr>
                <td colspan="4">
                    <?= $this->render_partial('course/room_requests/_request.php', ['request' => $rr]); ?>
                </td>
            </tr>
        <? endif ?>
        </tbody>
    </table>
<? else : ?>
    <?= MessageBox::info(_('Zu dieser Veranstaltung sind noch keine Raumanfragen vorhanden.')) ?>
<? endif ?>

<? if (Request::isXhr()) : ?>
    <div data-dialog-button>
        <?= \Studip\LinkButton::createEdit(_('Neue Raumanfrage erstellen'), $controller->url_for('course/room_requests/new/' . $course_id, $url_params), ['data-dialog' => 'size=big']) ?>
    </div>
<? endif ?>
