<?php use Studip\Button, Studip\LinkButton; ?>
<form method="POST" action="<?=URLHelper::getLink()?>#anker">
<?=CSRFProtection::tokenTag()?>
<?=ELearningUtils::getHeader(_("Neues Lernmodul erstellen"));?>
<? foreach($cms_types as $name => $value) : ?>
    <input type="HIDDEN" name="<?=$name?>" value="<?=htmlReady($value)?>">
<? endforeach ?>
<table border="0" cellspacing=0 cellpadding=6 width = "100%">
<tr><td>
    <font size="-1">
    <?=sprintf(_("Typ für neues Lernmodul: %s"), ELearningUtils::getTypeSelectbox($cms))?>
    </font>
</td>
<td align="right" valign="middle">
    <? if (count($types) > 1) : ?>
        <?=Button::create(_('Auswählen'), 'choose')?>
    <? endif ?>
    <?=$link?>
</td></tr></table>
</form>