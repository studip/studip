<? use Studip\Button, Studip\LinkButton; ?>

<?
    $send_as_email = [
        1 => _('nie'),
        2 => _('immer'),
        3 => _('wenn vom Absender gewünscht'),
    ];
    $mail_formats = [
        0 => _('Text'),
        1 => _('HTML'),
    ];
?>

<form action="<?= $controller->url_for('settings/messaging') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

    <fieldset>
        <legend>
            <?= _('Einstellungen des Nachrichtensystems') ?>
        </legend>

        <label>
            <input type="checkbox" value="1" name="save_snd" id="save_snd"
                <? if ($settings['save_snd'] == 1) echo 'checked'; ?>>
            <?= _('Gesendete Nachrichten im Postausgang speichern') ?>
        </label>

        <? if ($GLOBALS['MESSAGING_FORWARD_AS_EMAIL']): ?>
            <label>
                <input type="checkbox" value="1" name="request_mail_forward" id="request_mail_forward"
                    <? if ($settings['request_mail_forward'] == 1) echo 'checked'; ?>>
                <?= _('Gesendete Nachrichten auch als E-Mail verschicken') ?>
            </label>
        <? endif ?>

        <label>
            <input type="checkbox" value="1" name="logout_markreaded" id="logout_markreaded"
                <? if ($settings['logout_markreaded'] == 1) echo 'checked'; ?>>
            <?= _('Beim Logout alle Nachrichten als gelesen speichern') ?>
        </label>

        <? if ($GLOBALS['MESSAGING_FORWARD_AS_EMAIL']): ?>
            <label>
                <?= _('Kopie empfangener Nachrichten an eigene E-Mail-Adresse schicken') ?>
                <select name="send_as_email">
                <? foreach ($send_as_email as $key => $label): ?>
                    <option value="<?= htmlReady($key) ?>" <? if (($user->email_forward ?: $GLOBALS['MESSAGING_FORWARD_DEFAULT']) == $key) echo 'selected' ?>>
                        <?= htmlReady($label) ?>
                    </option>
                <? endforeach ?>
                </select>
            </label>

            <label>
                <?= _('E-Mail in folgendem Format versenden') ?>
                <select name="mail_format">
                    <? foreach ($mail_formats as $key => $label): ?>
                        <option value="<?= htmlReady($key) ?>" <? if ($config->getValue('MAIL_AS_HTML') == $key) echo 'selected' ?>>
                            <?= htmlReady($label) ?>
                        </option>
                    <? endforeach ?>
                </select>
            </label>
        <? endif ?>

        <div>
            <? if ($user->smsforward_rec):  // empfaenger ausgewaehlt ?>
                <?= _('Empfänger') ?>
                <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . get_username($user->smsforward_rec)) ?>">
                    <?= Avatar::getAvatar($user->smsforward_rec)->getImageTag(Avatar::SMALL) ?>
                    <?= get_fullname($user->smsforward_rec, 'full', true) ?>
                </a>
                <a href="<?= $controller->url_for('settings/messaging/verify/forward_receiver') ?>">
                    <?= Icon::create('trash', 'clickable')->asImg() ?>
                </a>
                <br>
                <label>
                    <input type="checkbox" value="1" name="smsforward_copy"
                        <? if ($user->smsforward_copy) echo 'checked'; ?>>
                    <?= _('Kopie im persönlichen Posteingang speichern.') ?>
                </label>
            <? else: ?>
                <label>
                    <?= _('Weiterleitung empfangener Nachrichten') ?>
                    <?= QuickSearch::get('new_smsforward_rec', new StandardSearch('username'))->withButton()->render() ?>
                </label>
            <? endif; ?>
        </div>

        <label style="clear:both;">
            <?= _('Signatur') ?>
            <textarea name="sms_sig" aria-label="<?= _('Signatur') ?>"><?= htmlready($settings['sms_sig']) ?></textarea>
        </label>

        <label>
            <?= _('Buddies/ Wer ist online?') ?>
            <select name="online_format">
                <? foreach ($GLOBALS['NAME_FORMAT_DESC'] as $key => $value): ?>
                    <option value="<?= $key ?>" <? if ($config->getValue('ONLINE_NAME_FORMAT') == $key) echo 'selected '; ?>>
                        <?= htmlReady($value) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>
    </fieldset>


    <footer>
        <?= Button::createAccept(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
        <?= LinkButton::create(_('Zurücksetzen'), $controller->url_for('settings/messaging/verify/reset'), ['title' => _('Einstellungen zurücksetzen')]) ?>
    </footer>
</form>
