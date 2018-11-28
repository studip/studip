<?php use Studip\Button, Studip\LinkButton; ?>
<? if (count($options) > 1) : ?>
    <select name="module_type_<?=htmlReady($cms)?>" style="vertical-align:middle">
    <option value=""><?=_("Bitte auswählen")?></option>
    <? foreach($options as $key => $name) : ?>
        <option value="<?=$key?>" <?=($selected == $key) ? ' selected' : ''?>>
            <?=htmlReady($name)?>
        </option>
    <? endforeach ?>
    </select>
<? else : ?>
    <? foreach($options as $key => $name) : ?>
        <?=htmlReady($name)?>
        <input type="HIDDEN" name="module_type_<?=htmlReady($cms)?>" value="<?=htmlReady($key)?>">
    <? endforeach ?>
<? endif ?>