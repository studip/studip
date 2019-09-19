<? if (!$valid_url) : ?>
<form class="default" action="<?= $controller->url_for('admin/ilias_interface/edit_server/'.$ilias_index) ?>" method="post">
<? else : ?>
<form class="default" action="<?= $controller->url_for('admin/ilias_interface/save/'.$ilias_index) ?>" method="post">
<? endif ?>
    <?= CSRFProtection::tokenTag() ?>
    <? if (count($existing_indices) && ($ilias_index == 'new')) : ?>
    <label>
        <span class="required"><?= _('Art der Verknüpfung') ?></span>
        <select name="ilias_index">
        <option value="new" selected><?=_('Neue Verknüpfung')?></option>
        <? foreach ($existing_indices as $existing_index => $data) : ?>
            <option value="<?=$existing_index?>"><?=sprintf(_('ILIAS-Installation (Index %s) aus vorheriger Verknüpfung'), $existing_index)?></option>
        <? endforeach ?>
        </select>
    </label>
    <? else : ?>
    <label>
        <span class="required"><?= _('Kennung der Verknüpfung') ?></span>
        <input type="hidden" name="ilias_index" value="<?=$ilias_index?>">
        <input type="text" size="50" maxlength="255" value="<?=$ilias_index == 'new' ? _('Neue Verknüpfung') : $ilias_index?>" disabled>
    </label>
    <? endif ?>
    <label>
        <span class="required"><?= _('Name der Installation') ?></span>
        <input type="text" name="ilias_name" size="50" maxlength="255" value="<?= htmlReady($ilias_config['name']) ?>" required>
    </label>
    <label>
        <span class="required">  <?= _('URL') ?></span>
        <input type="text" name="ilias_url" size="50" maxlength="255" value="<?= $ilias_config['url'] ?>" required>
    </label>
    <? if ($valid_url) : ?>
        <label for="ilias_version">
            <span class="required"><?= _('ILIAS Version') ?></span>
            <? if ($ilias_version) : ?>
                <input type="hidden" name="ilias_version" value="<?=htmlReady($ilias_version)?>">
                <div><?=htmlReady($ilias_version).' ('.htmlReady($ilias_version_date).')'?></div>
            <? else : ?>
                <input type="text" name="ilias_version" size="8" maxlength="8" value="<?=htmlReady($ilias_config['version']) ?>" required>
            <? endif ?>
        </label>
        <label>
            <span class="required">  <?= _('Name des ILIAS-Mandanten') ?></span>
            <? if (count($ilias_clients) == 1) : ?>
                <input type="hidden" name="ilias_client" value="<?=htmlReady($ilias_clients[0])?>">
                <div><?=htmlReady($ilias_clients[0])?></div>
            <? elseif (count($ilias_clients) > 1) : ?>
                <select name="ilias_client">
                <? foreach ($ilias_clients as $client_name) : ?>
                    <option value="<?=htmlReady($client_name)?>" <?= $client_name == $ilias_config['client'] ? ' selected' : ''?>><?=htmlReady($client_name)?></option>
                <? endforeach ?>
                </select>
            <? else : ?>
                <input type="text" name="ilias_client" size="50" maxlength="255" value="<?= $ilias_config['client'] ?>" required>
            <? endif ?>
        </label>
        <label>
            <span><?= _('LDAP-Einstellung') ?></span>
            <? if ($ldap_options) : ?>
                <select name="ilias_ldap_enable">
                <?=$ldap_options;?>
                </select><br>
                <?=_("Authentifizierungsplugin (nur LDAP) beim Anlegen von externen Accounts übernehmen.");?>
                <?=Icon::create('info-circle', 'inactive', ['title' => _("Wählen Sie hier ein Authentifizierungsplugin, damit neu angelegte ILIAS-Accounts den Authentifizierungsmodus LDAP erhalten, wenn dieser Modus auch für den vorhandenen Stud.IP-Account gilt. Andernfalls erhalten alle ILIAS-Accounts den default-Modus")])->asImg(16);?>
            <? else : ?>
                <br><?=_("(Um diese Einstellung zu nutzen muss zumindest ein LDAP Authentifizierungsplugin aktiviert sein.)");?>
                <input type="hidden" name="ilias_ldap_enable" value="">
            <? endif ?>
        </label>
        <label>
            <span class="required">  <?= _('Admin-Account') ?></span>
            <input type="text" name="ilias_admin" size="50" maxlength="255" value="<?= $ilias_config['admin'] ?>" required>
        </label>
        <label>
            <span class="required">  <?= _('Admin-Passwort') ?></span>
            <input type="password" name="ilias_admin_pw" size="50" maxlength="255" value="<?= $ilias_config['admin_pw'] ?>" required>
        </label>
    <? endif ?>
    <footer data-dialog-button>
        <? if (!$valid_url) : ?>
            <?= Studip\Button::createAccept(_('Weiter'), 'submit',  ['data-dialog' => 'size=auto']) ?>
        <? else : ?>
            <?= Studip\Button::createAccept(_('Speichern'), 'submit') ?>
        <? endif ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>