<? use Studip\Button, Studip\LinkButton; ?>

<form class="default" action="<?= $controller->url_for('admin/api/config') ?>" method="post">
    <fieldset>
        <legend><?= _('Konfiguration') ?></legend>

        <input type="hidden" name="active" value="0">
        <label>
            <input type="checkbox" name="active" value="1" <? if ($config['API_ENABLED']) echo 'checked'; ?>>
            <?= _('REST-API aktiviert') ?>
        </label>
        
        
        <label class="caption" for="auth">
            <?= _('Standard-Authentifizierung beim Login') ?>
            <select name="auth" id="auth">
            <? foreach ($GLOBALS['STUDIP_AUTH_PLUGIN'] as $plugin): ?>
                <option <? if ($config['API_OAUTH_AUTH_PLUGIN'] === $plugin) echo 'selected'; ?>>
                    <?= $plugin ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>
    </fieldset>
    <footer>
        <?= Button::createAccept(_('Speichern')) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/api')) ?>
    </footer>
</form>
