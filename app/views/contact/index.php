<? use \Studip\Button; ?>
<form action="<?= $controller->url_for('contact/edit_contact/' . $filter) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption>
            <?= htmlReady($title) ?>
            <span class='actions'>
                <?= $multiPerson ?>
                <? if ($filter): ?>
                    <a href="<?= $controller->url_for('contact/editGroup/' . $filter) ?>" data-dialog="size=auto"
                       title="<?= _('Gruppe bearbeiten') ?>">
                        <?= Icon::create('edit') ?>
                    </a>
                    <?= Icon::create('trash')->asInput(
                        ['formaction'   => $controller->url_for('contact/deleteGroup/' . $filter),
                         'title'        => _('Gruppe löschen'),
                         'data-confirm' => sprintf(_('Gruppe %s wirklich löschen?'), htmlReady($title))]) ?>
                <? endif; ?>
            </span>
        </caption>
        <thead>
            <tr>
                <th style="width:20px !important;">
                    <input aria-label="<?= _('Alle %s auswählen') ?>"
                           type="checkbox" name="all" value="1" data-proxyfor=":checkbox[name^=contact]">
                </th>
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
                        <th colspan="5">
                            <?= $header ?>
                        </th>
                    </tr>
                    <? foreach ($contactgroup as $contact): ?>
                        <tr id="contact_<?= $contact->id ?>">
                            <td>
                                <input aria-label="<?= _('Auswählen') ?>"
                                       type="checkbox" name="contact[<?= $contact->username ?>]" value="1"
                                    <? if (isset($flash['contacts']) && in_array($contact->id, $flash['contacts'])) echo 'checked'; ?>>
                            </td>
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
                                <? if (Config::get()->BLUBBER_GLOBAL_MESSENGER_ACTIVATE) : ?>
                                    <? $actionMenu->addLink(
                                        URLHelper::getURL('dispatch.php/blubber/write_to/' . $contact->user_id),
                                        _('Blubber diesen Nutzer an'),
                                        Icon::create('blubber'),
                                        ['data-dialog' => '']
                                    ) ?>
                                <? endif ?>
                                <? $actionMenu->addLink($controller->url_for('contact/vcard', ['user[]' => $contact->username]),
                                    _('vCard herunterladen'),
                                    Icon::create('vcard')) ?>
                                <?= $actionMenu->addButton('remove_person',
                                    $filter ? _('Kontakt aus Gruppe entfernen') : _('Kontakt entfernen'),
                                    Icon::create('person+remove',
                                        [
                                            'data-confirm' => sprintf(
                                                _('Wollen Sie %s wirklich von der Liste entfernen'),
                                                htmlReady($contact->username)
                                            ),
                                            'formaction'   => $controller->url_for('contact/remove/' . $filter, ['user' => $contact->username])
                                        ])
                                )->render() ?>
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
        <tfoot>
            <tr>
                <td colspan="5">
                    <select name="action_contact" id="contact_action" aria-label="<?= _('Aktion ausführen') ?>">
                        <option value="">- <?= _('Aktion auswählen') ?></option>
                        <option value="remove"><?= $filter ? _('Kontakte aus Gruppe entfernen') : _('Kontakte entfernen') ?></option>
                    </select>
                    <?= Button::create(_('Ausführen'), 'submit_action') ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
