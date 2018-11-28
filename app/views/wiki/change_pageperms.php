<form action="<?= URLHelper::getLink('wiki.php', ['view' => 'pageperms','lastpage' => $keyword]) ?>" method="post" class="default">

    <input type="hidden" name="keyword" value="<?= htmlReady($keyword) ?>">
    <? $storedStatusListEdit = ["autor"=>"","tutor"=>"","dozent"=>""]; ?>
    <? $storedStatusListRead = $storedStatusListEdit;?>
    <? $storedStatusListEdit[$storedStatusEdit] = "checked";?>
    <? $storedStatusListRead[$storedStatusRead] = "checked";?>
    <?
       if ($storedStatusRead == "autor" && $storedStatusEdit == "") {
           $statusGlobal = "checked";
           $storedStatusListEdit[$storedStatusStandard] = "checked";
           array_walk($storedStatusListRead, function(&$item) { $item .= " disabled"; });
           array_walk($storedStatusListEdit, function(&$item) { $item .= " disabled"; }); ?>
           <input type="radio" hidden id="autor_edit_standard" <?=$storedStatusListEdit["autor"]?>>
           <input type="radio" hidden id="tutor_edit_standard" <?=$storedStatusListEdit["tutor"]?>>
    <?  }
        if ($storedStatusRead == "tutor") {
            $storedStatusListEdit["autor"] .= " disabled";
        }
        if ($storedStatusRead == "dozent") {
            $storedStatusListEdit["autor"] .= " disabled";
            $storedStatusListEdit["tutor"] .= " disabled";
        }
    ?>

    <label>
        <input type="checkbox" name="page_global_perms" id="global" <?=$statusGlobal?>> <?=_("Standard Wiki-Einstellungen verwenden")?>
    </label>
    <br>

    <fieldset>
        <legend><?=_("Leseberechtigung")?></legend>
        <label>
            <input type="radio" name="page_read_perms" id="autor_read" value="autor" <?=$storedStatusListRead["autor"]?> title="<?=_('Wiki-Seite für alle Teilnehmende lesbar')?>" > <?=_("alle in der Veranstaltung")?>
        </label>
        <label>
            <input type="radio" name="page_read_perms" id="tutor_read" value="tutor" <?=$storedStatusListRead["tutor"]?> title="<?=_('Wiki-Seite nur eingeschränkt lesbar')?>" > <?=_("Lehrende und Tutor/innen")?>
        </label>
        <label>
            <input type="radio" name="page_read_perms" id="dozent_read" value="dozent" <?=$storedStatusListRead["dozent"]?> title="<?=_('Wiki-Seite nur eingeschränkt lesbar')?>" > <?=_("nur Lehrende")?>
        </label>
    </fieldset>

    <fieldset>
        <legend><?=_("Editierberechtigung")?></legend>
        <label>
            <input type="radio" name="page_edit_perms" id="autor_edit" value="autor" <?=$storedStatusListEdit["autor"]?> title="<?=_('editierbar nur, wenn für alle Teilnehmenden lesbar')?>" > <?=_("alle in der Veranstaltung")?>
        </label>
        <label>
            <input type="radio" name="page_edit_perms" id="tutor_edit" value="tutor" <?=$storedStatusListEdit["tutor"]?> title="<?=_('editierbar nur, wenn für diesen Personenkreis lesbar')?>" > <?=_("Lehrende und Tutor/innen")?>
        </label>
        <label>
            <input type="radio" name="page_edit_perms" id="dozent_edit" value="dozent" <?=$storedStatusListEdit["dozent"]?> title="<?=_('editierbar nur, wenn für diesen Personenkreis lesbar')?>" > <?=_("nur Lehrende")?>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), URLHelper::getURL('wiki.php', compact('keyword'))) ?>
    </footer>
</form>
