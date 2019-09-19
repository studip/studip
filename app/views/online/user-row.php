<tr>
    <td>
        <a href="<?= $controller->url_for('profile?username=' . $user['username']) ?>">
            <?= Avatar::getAvatar($user['user_id'], $user['username'])->getImageTag(Avatar::SMALL, [
                'title' => $user['name']
            ]) ?>
        </a>
    </td>
    <td>
        <a href="<?= $controller->url_for('profile?username=' . $user['username']) ?>">
            <?= htmlReady($user['name']) ?>
        </a>
    <? foreach (StudipKing::is_king($user['user_id'], true) as $text) : ?>
        <?= Icon::create('crown', 'sort', ['title' => $text]) ?>
    <? endforeach ?>
    </td>
    <td style="white-space: nowrap;">
        <?= ucfirst(reltime(time() - $user['last_action'])) ?>
    </td>
    <td class="actions" nowrap="nowrap">
        <? $actionMenu = ActionMenu::get() ?>
        <? if (class_exists('Blubber')) : ?>
            <? $actionMenu->addLink(
                URLHelper::getURL('plugins.php/blubber/streams/global', ['mention' => $user['username']]),
                _('Blubber diesen Nutzer an'),
                Icon::create('blubber', 'clickable')
            ) ?>
        <? endif ?>
        <? $actionMenu->addLink(
            URLHelper::getURL('dispatch.php/messages/write', ['rec_uname' => $user['username']]),
            _('Nachricht an Benutzer verschicken'),
            Icon::create('mail', 'clickable'),
            ['data-dialog' => 'size=50%']
        ) ?>

        <? if ($user['is_buddy']): ?>
            <? $actionMenu->addLink(
                $controller->url_for('online/buddy/remove?username=' . $user['username']),
                _('Aus den Kontakten entfernen'),
                Icon::create('person+remove', 'clickable')
            ) ?>
        <? else: ?>
            <? $actionMenu->addLink(
                $controller->url_for('online/buddy/add?username=' . $user['username']),
                _('Zu den Kontakten hinzufügen'),
                Icon::create('person+add', 'clickable')
            ) ?>
        <? endif; ?>
        <?= $actionMenu->render() ?>
    </td>
</tr>
