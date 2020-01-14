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
                    <?= htmlReady($rr->getTypeString(), 1, 1) ?>
                </td>
                <td>
                    <?= htmlReady($rr->user ? $rr->user->getFullName() : '') ?>
                </td>
                <td>
                    <?= htmlReady($rr->getStatusText()) ?>
                </td>
                <td class="actions">
                    <a class="load-in-new-row"
                       href="<?= $controller->link_for('course/room_requests/info/' . $rr->id) ?>">
                        <?= Icon::create('info', 'clickable', ['title' => _('Weitere Informationen einblenden')])->asImg(16) ?>
                    </a>
                    <? $params = [] ?>
                    <? $dialog = []; ?>
                    <? if (Request::isXhr()) : ?>
                        <? $params['asDialog'] = true; ?>
                        <? $dialog['data-dialog'] = 'size=big' ?>
                    <? endif ?>

                    <? $actionMenu = ActionMenu::get() ?>
                    <? $actionMenu->addLink(
                        $controller->url_for('course/room_requests/edit/' . $rr->id),
                        _('Diese Anfrage bearbeiten'),
                        Icon::create('edit', 'clickable', ['title' => _('Diese Anfrage bearbeiten')]),
                        $dialog
                    ) ?>

                    <? $user_has_permissions = false;
                    $user = User::findCurrent();
                    if ($rr->room) {
                        $user_has_permissions = (
                            $rr->room->userHasPermission($user, 'admin') &&
                            (RoomRequest::countBySql(
                                "id = :request_id
                                AND closed = '0'
                                AND user_id = :user_id",
                                [
                                    'request_id' => $rr->id,
                                    'user_id' => $GLOBALS['user']->id
                                ]
                            ) > 0
                            )
                        );
                    } else {
                        $user_has_permissions = ResourceManager::userHasGlobalPermission($user, 'admin');
                    } ?>
                    <? if ($user_has_permissions): ?>
                        <? $actionMenu->addLink(
                            URLHelper::getLink(
                                'dispatch.php/resources/room_request/resolve/' . $rr->id
                            ),
                            _('Diese Anfrage selbst auflösen'),
                            Icon::create(
                                'admin',
                                'clickable',
                                [
                                    'title' => _('Diese Anfrage selbst auflösen')
                                ]
                            ),
                            ['data-dialog' => '1']
                        ) ?>
                    <? endif ?>
                    <? $actionMenu->addLink(
                        $controller->url_for('course/room_requests/delete/' . $rr->id),
                        _('Diese Anfrage zurückziehen'),
                        Icon::create(
                            'trash',
                            'clickable',
                            [
                                'title' => _('Diese Anfrage zurückziehen')
                            ]
                        )
                    ) ?>
                    <?= $actionMenu->render() ?>
                </td>
            </tr>
        <? endforeach ?>
        <? if ($request_id == $rr->id) : ?>
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
