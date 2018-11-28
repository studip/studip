<?php use Studip\Button, Studip\LinkButton; ?>
<form method="POST" action="<?=URLHelper::getLink()?>#anker" class="default">
    <?=CSRFProtection::tokenTag()?>
    <fieldset>
        <?= ELearningUtils::getHeader(_("Suche")) ?>

        <input type="HIDDEN" name="anker_target" value="search">
        <input type="HIDDEN" name="view" value="<?=htmlReady($view)?>">
        <input type="HIDDEN" name="cms_select" value="<?=htmlReady($cms_select)?>">

        <label>
            <?= htmlReady($message) ?>
            <input name="search_key" type="text" value="<?=htmlReady($search_key)?>">
        </label>
    </fieldset>

    <footer>
        <?=Button::create(_('Suchen'))?>
    </footer>
</form>
