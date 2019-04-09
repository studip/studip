<?php use Studip\Button, Studip\LinkButton; ?>
<a name="anker"></a>
<form method="POST" action="<?=URLHelper::getLink()?>#anker">
<input type="HIDDEN" name="view" value="<?=htmlReady($view)?>">
<input type="HIDDEN" name="ref_id" value="<?=htmlReady($ref_id)?>">
<input type="HIDDEN" name="module_type" value="<?=htmlReady($module_type)?>">
<input type="HIDDEN" name="new_account_step" value="<?=htmlReady($new_account_step)?>">
<input type="HIDDEN" name="new_account_cms" value="<?=htmlReady($new_account_cms)?>">
<input type="HIDDEN" name="cms_select" value="<?=htmlReady($cms_select)?>">
<?=CSRFProtection::tokenTag()?>
<table border="0" cellspacing=0 cellpadding=0 width = "99%">
<? if ($is_verified) : ?>
<tr>
    <td class="table_row_even" align="left" valign="middle" colspan="2">
    <? if ($module_title) : ?>
        <?=sprintf( _('Hier gelangen Sie zum gewählten Lernmodul "%s":'), htmlReady($module_title) )?>
        <br>
        <br>
        <?=$module_links?>
        <br>
        <br>
    <? endif ?>
    </td>
</tr>
<? elseif ($step == 'assign') : ?>
    <tr>
        <td class="table_row_even" align="left" valign="middle" colspan="2">
            <br>
            <font size="-1">
            <?=sprintf(_("Geben Sie nun Benutzernamen und Passwort Ihres Benutzeraccounts in %s ein."),  htmlReady($cms_title))?>
            </font>
            <br>
        </td>
    </tr>
    <tr>
        <td class="table_row_even" align="right" valign="middle" colspan="2">
            <br>
        </td>
    </tr>
    <tr>
        <td class="table_row_even" align="right" valign="middle" width="20%">
            <font size="-1">
                <?=_("Benutzername:")?>&nbsp;
            </font>
        </td>
        <td class="table_row_even" align="left" valign="middle">
            <input name="ext_username" size="30" style="vertical-align:middle;font-size:9pt;" value="<?=htmlReady($ext_username)?>">
        </td>
    </tr>
    <tr>
        <td class="table_row_even" align="right" valign="middle" colspan="2">
            <br>
        </td>
    </tr>
    <tr>
        <td class="table_row_even" align="right" valign="middle" width="20%">
            <font size="-1">
                <?=_("Passwort:")?>&nbsp;
            </font>
        </td>
        <td class="table_row_even" align="left" valign="middle">
            <input name="ext_password" type="PASSWORD" size="30" style="vertical-align:middle;font-size:9pt;" value="">
        </td>
    </tr>
    <tr>
        <td class="table_row_even">
        </td>
        <td class="table_row_even" align="left" valign="middle">
            <br>
            <?=Button::createAccept(_('Bestätigen'), 'next')?>
            <br>
        </td>
    </tr>
    <tr>
        <td align="center" valign="middle" colspan="2">
            <br>
            <input type="HIDDEN" name="assign" value="1">
            <?=Button::create('<< ' . _('Zurück'), 'go_back')?>
        </td>
    </tr>
<? elseif ($step == 'new_account') : ?>
    <tr>
        <td class="table_row_even" align="left" valign="middle" colspan="2">
            <br>
            <font size="-1">
            <?=sprintf(_("Geben Sie nun ein Passwort für Ihren neuen Benutzeraccount in %s ein."),  htmlReady($cms_title))?>
            </font>
            <br>
        </td>
    </tr>
    <tr>
        <td class="table_row_even" align="right" valign="middle" colspan="2">
            <br>
        </td>
    </tr>
    <tr>
        <td class="table_row_even" align="right" valign="middle" width="20%">
            <font size="-1">
                <?=_("Passwort:")?>&nbsp;
            </font>
        </td>
        <td class="table_row_even" align="left" valign="middle">
            <input name="ext_password" type="PASSWORD" size="30" style="vertical-align:middle;font-size:9pt;" value="">
        </td>
    </tr>
    <tr>
        <td class="table_row_even" align="right" valign="middle" colspan="2">
            <br>
        </td>
    </tr>
    <tr>
        <td class="table_row_even" align="right" valign="middle" width="20%">
            <font size="-1">
                <?=_("Passwort-Wiederholung:")?>&nbsp;
            </font>
        </td>
        <td class="table_row_even" align="left" valign="middle">
            <input name="ext_password_2" type="PASSWORD" size="30" style="vertical-align:middle;font-size:9pt;" value="">
        </td>
    </tr>
    <tr>
        <td class="table_row_even">
        </td>
        <td class="table_row_even" align="left" valign="middle">
            <br>
            <?=Button::createAccept(_('Bestätigen'), 'next')?>
            <br>
        </td>
    </tr>
    <tr>
        <td align="center" valign="middle" colspan="2">
            <br>
            <input type="HIDDEN" name="assign" value="1">
            <?=Button::create('<< ' . _('Zurück'), 'go_back')?>
        </td>
    </tr>
<? else : ?>
    <tr>
    <td class="table_row_even" align="left" valign="middle" colspan="2">
    <font size="-1">
    <? if ($is_connected) : ?>
        <?=sprintf(_("Ihr Stud.IP-Account wurde bereits mit einem %s-Account verknüpft. Wenn Sie den verknüpften "
                    ."Account durch einen anderen, bereits existierenden Account ersetzen wollen, klicken Sie auf "
                    ."\"zuordnen\"."), $cms_title)?>
        <br>
        <br>
    <? else :?>
        <?=sprintf(_("Wenn Sie innerhalb von %s bereits über einen BenutzerInnen-Account verfügen, können Sie ihn "
                    ."jetzt \"zuordnen\". Anderenfalls wird automatisch ein neuer Account in %s für Sie erstellt, "
                    ."wenn Sie auf \"weiter\" klicken."), $cms_title, $cms_title)?>
        <br>
        <br>
    <? endif ?>

    <center>
    <?=Button::create('<< ' . _('Zurück'), 'go_back')?>
    <?=Button::create(_('Zuordnen'), 'assign', ['title' => _('Bestehenden Account zuordnen')])?>
    <? if (! $is_connected) : ?>
        <?=Button::create(_('Weiter') . ' >>', 'next')?>
    <? endif ?>
    </center>
    </font>
    </td>
    </tr>
<? endif ?>
</table>
</form>