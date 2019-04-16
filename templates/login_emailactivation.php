<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;
?>
<div align="center">
<table width="70%" border=0 cellpadding=0 cellspacing=0>
<tr><td class="table_header_bold" colspan=3 align="left">
 <?= Icon::create('mail', 'info_alt')->asImg(['class' => 'text-top']) ?>
 <b><?= _('E-Mail Aktivierung') ?></b>
</td></tr>
<tr><td style="background-color: #fff; padding: 1.5em;">
<?= _('Sie haben Ihre E-Mail-Adresse geändert. Um diese frei zu schalten müssen Sie den Ihnen an Ihre neue Adresse zugeschickten Aktivierungs Schlüssel im unten stehenden Eingabefeld eintragen.'); ?>
<br><form action="activate_email.php" method="post">
 <?= CSRFProtection::tokenTag() ?>
 <input name="key">
 <input name="uid" type="hidden" value="<?= $uid ?>">
 <?= Button::createAccept(_('Abschicken')) ?></form><br><br>
</td></tr></table></div><br>


<div align="center">
<table width="70%" border=0 cellpadding=0 cellspacing=0>
<tr><td class="table_header_bold" colspan=3 align="left">
 <?= Icon::create('mail', 'info_alt')->asImg(['class' => 'text-top']) ?>
 <b><?= _('E-Mail Aktivierung neu senden') ?></b>
</td></tr>
<tr><td style="background-color: #fff; padding: 1.5em;">
<?= _('Sollten Sie keine E-Mail erhalten haben, können Sie sich einen neuen Aktivierungsschlüssel zuschicken lassen. Geben Sie dazu Ihre gewünschte E-Mail-Adresse unten an:'); ?>
<form action="activate_email.php" method="post">
<?= CSRFProtection::tokenTag() ?>
<input type="hidden" name="uid" value="<?= $uid ?>">
<table><tr><td><?= _('E-Mail') ?>:</td><td><input name="email1"></td></tr>
<tr><td><?= _('Wiederholung') ?>:</td><td><input name="email2"></td></tr></table>
<?= Button::createAccept(_('Abschicken'))  ?>
</form>
</td></tr></table></div><br>
