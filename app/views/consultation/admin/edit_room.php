<form action="<?= $controller->store_room($block, $page) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Ort des Sprechstundenblocks bearbeiten') ?></legend>

        <label>
            <span class="required"><?= _('Ort') ?></span>
            <input required type="text" name="room" placeholder="<?= _('Ort') ?>"
                   value="<?= htmlReady($block->room) ?>">
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->indexURL()
        ) ?>
    </footer>
</form>
