<?php
use Studip\Button, Studip\LinkButton;

Helpbar::get()->addPlainText(_('Info'), "Personenlisten dienen dazu, um Sonderfälle erfassen zu ".
                                        "können, die in Anmeldeverfahren gesondert behandelt ".
                                        "werden sollen (Härtefälle etc.).");
Helpbar::get()->addPlainText(_('Info'), "Stellen Sie hier ein, wie die Chancen bei der ".
                                        "Platzverteilung verändert werden sollen. Ein Wert ".
                                        "von 1 bedeutet normale Verteilung, ein Wert kleiner ".
                                        "als 1 führt zur Benachteiligung, mit einem Wert ".
                                        "größer als 1 werden die betreffenden Personen ".
                                        "bevorzugt.");
?>
<?= $this->render_partial('dialog/confirm_dialog') ?>
<?= $error ? $error : '' ?>
<form class="default" action="<?= $controller->url_for('admission/userlist/save', $userlist_id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= ($userlist_id) ? _('Personenliste bearbeiten') : _('Personenliste anlegen') ?>
        </legend>
        <label>
            <span class="required">
                <?= _('Name der Personenliste') ?>
            </span>
            <input type="text" size="60" maxlength="255" name="name" value="<?= $userlist ? htmlReady($userlist->getName()) : '' ?>" required>
        </label>
        <br/>
        <label for="factor">
            <?= _('Wie sollen die Personen auf dieser Liste bei der Platzverteilung berücksichtigt werden?') ?>
        </label>
        <label for="factor-0">
            <input type="radio" name="factor" id="factor-0" value="0"<?= $userlist->getFactor() == 0 ? ' checked' : '' ?>>
            <?= _('Nachrangig (nach allen anderen Personen in der Platzvergabe)') ?>
        </label>
        <label for="factor-max">
            <input type="radio" name="factor" id="factor-max"
                   value="<?= (float) PHP_INT_MAX ?>"<?= $userlist->getFactor() == PHP_INT_MAX ? ' checked' : '' ?>>
            <?= _('Bevorzugt (erhalten zuerst einen Platz)') ?>
        </label>
        <table class="default">
            <caption>
                <?= _('Personen') ?>
                <span class="actions">
                    <?= MultiPersonSearch::get('add_userlist_member_' . $userlist_id)
                        ->setTitle(_('Personen zur Liste hinzufügen'))
                        ->setSearchObject($userSearch)
                        ->setDefaultSelectedUser(array_map(function ($u) { return $u->id; }, $users))
                        ->setDataDialogStatus(Request::isXhr())
                        ->setJSFunctionOnSubmit(Request::isXhr() ? 'jQuery(this).closest(".ui-dialog-content").dialog("close");' : false)
                        ->setExecuteURL($controller->url_for('admission/userlist/add_members', $userlist_id))
                        ->render() ?>
                </span>
            </caption>
            <thead>
                <tr>
                    <th></th>
                    <th><?= _('Name') ?></th>
                    <th class="actions"><?= _('Aktion') ?></th>
                </tr>
            </thead>
            <tbody>
            <? if (count($users) === 0): ?>
                <tr>
                    <td colspan="3">
                        <?= _('Niemand ist in die Liste eingetragen.') ?>
                    </td>
                </tr>
            <? else: $i = 1; ?>
                <? foreach ($users as $u) : ?>
                    <tr>
                        <td>
                            <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $u->username) ?>">
                                <?= Avatar::getAvatar($u->id, $u->username)->getImageTag(Avatar::SMALL) ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $u->username) ?>">
                                <?= $u->getFullname('full_rev') . ' (' . $u->username . ')' ?>
                            </a>
                            <input type="hidden" name="users[]" value="<?= $u->id ?>"/>
                        </td>
                        <td class="actions">
                            <a href="<?= $controller->url_for('admission/userlist/delete_member',
                                $userlist_id, $u->id) ?>" class="userlist-delete-user"
                                data-confirm="<?= sprintf(_('Soll %s wirklich von der Liste entfernt werden?'),
                                    $u->getFullname()) ?>">
                                <?= Icon::create('trash', 'clickable') ?>
                            </a>
                        </td>
                    </tr>
                <? endforeach; ?>
            <? endif; ?>
            </tbody>
        </table>
    </fieldset>

    <footer>
        <?= Button::createAccept(_('Speichern'), 'submit') ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admission/userlist')) ?>
    </footer>
</form>
