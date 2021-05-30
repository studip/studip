<form class="default" action="<?=$controller->link_for('/edittool/plugin_' . $tool->plugin_id)?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <label for="displayname">
            <?=_('Name des Werkzeugs')?>
            <input type="text" name="displayname" id="displayname" value="<?=htmlReady($tool->getDisplayname())?>">
        </label>

        <label><?=_('Sichtbarkeit')?></label>
        <div class="hgroup">
            <label for="permission_autor">
                <?=_('Studierende')?>
                <input type="radio" name="permission" id="permission_autor" value="autor" checked>
            </label>
            <label for="permission_tutor">
                <?=_('Lehrende')?>
                <input type="radio" name="permission" id="permission_tutor" value="tutor" <?= $tool->getVisibilityPermission() === 'tutor' ? 'checked' : '' ?>>
            </label>
        </div>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
    </footer>
</form>
