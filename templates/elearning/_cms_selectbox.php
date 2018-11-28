<?php use Studip\Button, Studip\LinkButton; ?>
<form method="POST" action="<?=URLHelper::getLink()?>#anker" class="default">
    <?=CSRFProtection::tokenTag()?>
    <fieldset>
        <?= ELearningUtils::getHeader(_("Angebundenes System")); ?>

        <input type="HIDDEN" name="anker_target" value="choose">
        <input type="HIDDEN" name="view" value="<?=htmlReady($view)?>">
        <input type="HIDDEN" name="search_key" value="<?=htmlReady($search_key)?>">

        <label>
            <?=htmlReady($message)?>
            <select name="cms_select" style="vertical-align:middle">
                <option value=""><?=_("Bitte auswählen")?></option>
                <? foreach($options as $key => $name) : ?>
                    <option value="<?=$key?>" <?=($cms_select == $key) ? ' selected' : ''?>>
                        <?=htmlReady($name)?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
    </fieldset>

    <footer>
        <?= Button::create(_('Auswählen')) ?>
    </footer>
</form>
