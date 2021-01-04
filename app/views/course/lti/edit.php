<form class="default" action="<?= $controller->link_for('course/lti/save/' . $lti_data->position) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Einstellungen') ?>
        </legend>

        <label>
            <span class="required">
                <?= _('Titel') ?>
            </span>
            <input type="text" name="title" value="<?= htmlReady($lti_data->title) ?>" required>
        </label>

        <label>
            <?= _('Beschreibung') ?>
            <textarea name="description" class="add_toolbar wysiwyg"><?= wysiwygReady($lti_data->description) ?></textarea>
        </label>

        <label>
            <?= _('Auswahl des externen Tools') ?>
            <select class="config_tool" name="tool_id">
                <? foreach ($tools as $tool): ?>
                    <option value="<?= $tool->id ?>"
                        <? if ($tool->allow_custom_url): ?>
                            data-url="<?= htmlReady($tool->launch_url) ?>"
                        <? endif ?>
                        <?= $lti_data->tool_id == $tool->id ? 'selected' : '' ?>><?= htmlReady($tool->name) ?></option>
                <? endforeach ?>
                <option value="0" <?= $lti_data && $lti_data->tool_id == 0 ? 'selected' : '' ?>><?= _('Zugangsdaten selbst eingeben...') ?></option>
            </select>
        </label>

        <div class="config_custom_url">
            <label>
                <?= _('URL der Anwendung (optional)') ?>
                <?= tooltipIcon(_('Sie können direkt auf eine URL in der Anwendung verlinken.')) ?>
                <input type="text" name="custom_url" value="<?= htmlReady($lti_data->launch_url) ?>">
            </label>
        </div>

        <div class="config_launch_url">
            <label>
                <?= _('URL der Anwendung') ?>
                <?= tooltipIcon(_('Die Betreiber dieses Tools müssen Ihnen eine URL und Zugangsdaten (Consumer-Key und Consumer-Secret) mitteilen.')) ?>
                <input type="text" name="launch_url" value="<?= htmlReady($lti_data->launch_url) ?>">
            </label>

            <label>
                <?= _('Consumer-Key des LTI-Tools') ?>
                <input type="text" name="consumer_key" value="<?= htmlReady($lti_data->options['consumer_key']) ?>">
            </label>

            <label>
                <?= _('Consumer-Secret des LTI-Tools') ?>
                <input type="text" name="consumer_secret" value="<?= htmlReady($lti_data->options['consumer_secret']) ?>">
            </label>

            <label>
                <?= _('OAuth Signatur Methode des LTI-Tools') ?>
                <select name="oauth_signature_method">
                    <option value="sha1">HMAC-SHA1</option>
                    <option value="sha256" <?=$lti_data->options['oauth_signature_method'] === 'sha256' ? 'selected' : '' ?>>HMAC-SHA256</option>
                </select>
            </label>

            <label>
                <input type="checkbox" name="send_lis_person" value="1" <?= $lti_data->options['send_lis_person'] ? ' checked' : '' ?>>
                <?= _('Nutzerdaten an LTI-Tool senden') ?>
                <?= tooltipIcon(_('Nutzerdaten dürfen nur an das externe Tool gesendet werden, wenn es keine Datenschutzbedenken gibt. Mit Setzen des Hakens bestätigen Sie, dass die Übermittlung der Daten zulässig ist.')) ?>
            </label>
        </div>

        <label>
            <input type="checkbox" name="document_target" value="iframe" <?= $lti_data->options['document_target'] == 'iframe' ? ' checked' : '' ?>>
            <?= _('Anzeige im IFRAME auf der Seite') ?>
            <?= tooltipIcon(_('Normalerweise wird das externe Tool in einem neuen Fenster angezeigt. Aktivieren Sie diese Option, wenn die Anzeige stattdessen in einem IFRAME erfolgen soll.')) ?>
        </label>

        <label>
            <?= _('Zusätzliche LTI-Parameter') ?>
            <?= tooltipIcon(_('Ein Wert pro Zeile, Beispiel: Review:Chapter=1.2.56')) ?>
            <textarea name="custom_parameters"><?= htmlReady($lti_data->options['custom_parameters']) ?></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('course/lti')) ?>
    </footer>
</form>

<script>
    $('.config_tool').change(function() {
        var url = $(this).find(':selected').data('url');

        if ($(this).val() == 0) {
            $('.config_launch_url').show();
        } else {
            $('.config_launch_url').hide();
        }

        if (url) {
            $('.config_custom_url').find('input').attr('placeholder', url);
            $('.config_custom_url').show();
        } else {
            $('.config_custom_url').hide();
        }
    }).trigger('change');
</script>
