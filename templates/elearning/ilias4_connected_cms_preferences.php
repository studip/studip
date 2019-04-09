<?php
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

if ($messages["error"] != "") {
    echo MessageBox::error($messages["error"]);
}
?>
<table>
    <tbody>
        <tr valign="top">
            <td width="30%" style="text-align: left; font-weight: bold;">
            <?=_("SOAP-Verbindung:")?>
            </td>
            <td>
            <? if ($soap_error) {
                echo _("Beim Herstellen der SOAP-Verbindung trat folgender Fehler auf:") . "<br><br>" . $soap_error;
            } else {
                echo sprintf(_("Die SOAP-Verbindung zum Klienten \"%s\" wurde hergestellt, der Name des Administrator-Accounts ist \"%s\"."), $soap_data["client"], $soap_data["username"]);
            }?>
            </td>
        </tr>
        <tr>
            <td width="30%" style="text-align: left; font-weight: bold;">
            <?=_("Kategorie:")?>
            </td>
            <td><input size="20" value="<?=$main_category_node_id_title?>" name="cat_name" type="text">
            &nbsp; <?=Icon::create('info-circle', 'inactive', ['title' => _("Geben Sie hier den Namen einer bestehenden ILIAS 4 - Kategorie ein, in der die Lernmodule und User-Kategorien abgelegt werden sollen.")])->asImg(16)?>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>(ID <?=$main_category_node_id?>)</td>
        </tr>
        <tr>
            <td width="30%" style="text-align: left; font-weight: bold;">
            <?=_("Kategorie für Userdaten:")?>
            </td>
            <td><?=$user_category_node_id_title?></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>(ID <?=$user_category_node_id?>)</td>
        </tr>
        <tr>
            <td width="30%" style="text-align: left; font-weight: bold;">
            <?=_("Rollen-Template für die persönliche Kategorie:")?>
            </td>
            <td><input size="20" value="<?=$user_role_template_name ?>" name="role_template_name" type="text">
            &nbsp; <?=Icon::create('info-circle', 'inactive', ['title' => _("Geben Sie den Namen des Rollen-Templates ein, das für die persönliche Kategorie von Lehrenden verwendet werden soll (z.B. \"Author\").")])->asImg(16)?>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>(ID <?=$user_role_template_id ?>)</td>
        </tr>
        <tr>
            <td width="30%" style="text-align: left; font-weight: bold;">
            <?=_("Passwörter:")?>
            </td>
            <td><input value="md5" name="encrypt_passwords" type="checkbox" <?=($encrypt_passwords ? 'checked' : '') ?>>
            &nbsp; <?=_("ILIAS-Passwörter verschlüsselt speichern.")?>
            <?=Icon::create('info-circle', 'inactive', ['title' => _("Wählen Sie diese Option, wenn die ILIAS-Passwörter der zugeordneten Accounts verschlüsselt in der Stud.IP-Datenbank abgelegt werden sollen.")])->asImg(16)?>
            </td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td width="30%" style="text-align: left; font-weight: bold;">
            <?=_("LDAP-Einstellung:")?>
            </td>
            <td><?
            if ($ldap_options) {
                echo '<select name="ldap_enable">';
                echo $ldap_options;
                echo '</select><br>';
                echo _("Authentifizierungsplugin (nur LDAP) beim Anlegen von externen Accounts übernehmen.");
                echo Icon::create('info-circle', 'inactive', ['title' => _("Wählen Sie hier ein Authentifizierungsplugin, damit neu angelegte ILIAS-Accounts den Authentifizierungsmodus LDAP erhalten, wenn dieser Modus auch für den vorhandenen Stud.IP-Account gilt. Andernfalls erhalten alle ILIAS-Accounts den default-Modus")])->asImg(16);
            } else {
                echo _("(Um diese Einstellung zu nutzen muss zumindest ein LDAP Authentifizierungsplugin aktiviert sein.)");
                echo '<input type="hidden" name="ldap_enable" value="">';
            }
            ?></td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
    </tbody>
</table>
<div style="text-align: center"><?= Button::create(_('Übernehmen'), 'submit', ['title' =>_("Einstellungen übernehmen")])?>
</div>
<div style="margin-top: 2em;"><?=$module_types?></div>
