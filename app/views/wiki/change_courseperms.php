<form action="<?= URLHelper::getLink('wiki.php', ['view' => 'courseperms', 'lastpage' => $keyword]) ?>" method="post" class="default">

    <fieldset>
        <input type="hidden" name="keyword" value="<?= htmlReady($keyword) ?>">
        <strong><?=_("Editierberechtigung")?></strong>
        <? $storedStatusList = ["autor"=>"","tutor"=>""]; ?>
        <? $storedStatusList[$storedStatus] = "checked";?>
        <label>
            <input type="radio" name="courseperms" id="alle" value="autor" <?=$storedStatusList["autor"]?>> <?=_("alle in der Veranstaltung")?>
        </label>
        <label>
            <input type="radio" name="courseperms" id="tutoren" value="tutor" <?=$storedStatusList["tutor"]?>> <?=_("Lehrende und Tutor/innen")?>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), URLHelper::getURL('wiki.php', compact('keyword'))) ?>
    </footer>
</form>
