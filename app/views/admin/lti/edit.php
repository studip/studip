<form class="default" action="<?= $controller->link_for('admin/lti/save/' . $tool->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Konfiguration des LTI-Tools') ?>
        </legend>

        <label>
            <span class="required">
                <?= _('Name der Anwendung') ?>
            </span>
            <input type="text" name="name" value="<?= htmlReady($tool->name) ?>" required>
        </label>

        <label>
            <span class="required">
                <?= _('URL der Anwendung') ?>
            </span>
            <input type="text" name="launch_url" value="<?= htmlReady($tool->launch_url) ?>" required>
        </label>

        <label>
            <span class="required">
                <?= _('Consumer-Key') ?>
            </span>
            <input type="text" name="consumer_key" value="<?= htmlReady($tool->consumer_key) ?>" required>
        </label>

        <label>
            <span class="required">
                <?= _('Consumer-Secret') ?>
            </span>
            <input type="text" name="consumer_secret" value="<?= htmlReady($tool->consumer_secret) ?>" required>
        </label>

        <label>
            <?= _('OAuth Signatur Methode') ?>
            <select name="oauth_signature_method">
                <option value="sha1">HMAC-SHA1</option>
                <option value="sha256" <?=$tool->oauth_signature_method === 'sha256' ? 'selected' : '' ?>>HMAC-SHA256</option>
            </select>
        </label>

        <label>
            <input type="checkbox" name="allow_custom_url" value="1" <?= $tool->allow_custom_url ? ' checked' : '' ?>>
            <?= _('Eingabe einer abweichenden URL im Kurs erlauben') ?>
        </label>

        <label>
            <input type="checkbox" name="deep_linking" value="1" <?= $tool->deep_linking ? ' checked' : '' ?>>
            <?= _('Auswahl von Inhalten über LTI Deep Linking (Content Item)') ?>
        </label>

        <label>
            <input type="checkbox" name="send_lis_person" value="1" <?= $tool->send_lis_person ? ' checked' : '' ?>>
            <?= _('Nutzerdaten an LTI-Tool senden') ?>
            <?= tooltipIcon(_('Nutzerdaten dürfen nur an das externe Tool gesendet werden, wenn es keine Datenschutzbedenken gibt. Mit Setzen des Hakens bestätigen Sie, dass die Übermittlung der Daten zulässig ist.')) ?>
        </label>

        <label>
            <?= _('Zusätzliche LTI-Parameter') ?>
            <?= tooltipIcon(_('Ein Wert pro Zeile, Beispiel: Review:Chapter=1.2.56')) ?>
            <textarea name="custom_parameters"><?= htmlReady($tool->custom_parameters) ?></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/lti')) ?>
    </footer>
</form>
