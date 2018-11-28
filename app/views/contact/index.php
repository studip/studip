<form method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption>
            <?= htmlReady($title) ?>
            <span class='actions'>
                <?= $multiPerson ?>
                <? if ($filter): ?>
                    <a href="<?= $controller->url_for('contact/editGroup/' . $filter) ?>" data-dialog="size=auto"
                       title="<?= _('Gruppe bearbeiten') ?>">
                        <?= Icon::create('edit', 'clickable')->asImg(16) ?>
                    </a>
                    <?= Icon::create('trash', 'clickable')->asInput(16,
                            ['formaction'   => $controller->url_for('contact/deleteGroup/' . $filter),
                             'title'        => _('Gruppe löschen'),
                             'data-confirm' => sprintf(_('Gruppe %s wirklich löschen?'), htmlReady($title))]) ?>
                <? endif; ?>
            </span>
        </caption>
        <thead>
            <tr>
                <th>
                    <?= _('Name') ?>
                </th>
                <th class="hidden-small-down">
                    <?= _('Stud.IP') ?>
                </th>
                <th class="hidden-small-down">
                    <?= _('E-Mail') ?>
                </th>
                <th class="actions">
                    <?= _('Aktionen') ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <? if (!empty($contacts))  : ?>
                <? foreach ($contacts as $header => $contactgroup): ?>
                    <tr id="letter_<?= $header ?>">
                        <th colspan="4">
                            <?= $header ?>
                        </th>
                    </tr>
                    <? foreach ($contactgroup as $contact): ?>
                        <tr id="contact_<?= $contact->id ?>">
                            <td>
                                <?= ObjectdisplayHelper::avatarlink($contact) ?>
                            </td>
                            <td class="hidden-small-down">
                                <a data-dialog="button"
                                   href="<?= URLHelper::getLink('dispatch.php/messages/write', ['rec_uname' => $contact->username]) ?>">
                                    <?= htmlReady($contact->username) ?>
                                </a>
                            </td>
                            <td class="hidden-small-down">
                                <a href="mailto:<?= htmlReady($contact->email) ?>">
                                    <?= htmlReady($contact->email) ?>
                                </a>
                            </td>
                            <td class="actions">
                                <? $actionMenu = ActionMenu::get() ?>
                                <? $actionMenu->addLink($controller->url_for('contact/vcard', ['user[]' => $contact->username]),
                                        _('vCard herunterladen'),
                                        Icon::create('vcard', 'clickable')) ?>
                                <? $actionMenu->addButton('remove_person',
                                        $filter ? _('Kontakt aus Gruppe entfernen') : _('Kontakt entfernen'),
                                        Icon::create('person+remove', 'clickable',
                                                ['data-confirm' => sprintf(_('Wollen Sie %s wirklich von der Liste entfernen'), htmlReady($contact->username)),
                                                 'formaction'   => $controller->url_for('contact/remove/' . $filter, ['user' => $contact->username])])) ?>
                                <?= $actionMenu->render() ?>
                            </td>
                        </tr>
                    <? endforeach; ?>
                <? endforeach; ?>
            <? else : ?>
                <tr>
                    <td colspan="4" style="text-align: center">
                        <?= $filter ? _('Keine Kontakte in der Gruppe vorhanden') : _('Keine Kontakte vorhanden') ?>
                    </td>
                </tr>
            <? endif ?>
        </tbody>
    </table>
</form>
