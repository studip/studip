<? if (!$slot_id): ?>
    <?= MessageBox::info(
        _('Das Ändern der Anmerkung wird auch die Anmerkung aller Termine dieses Blocks ändern.')
    )->hideClose() ?>
<? endif; ?>

<form action="<?= $controller->note($block, $slot_id ?: 0, $page) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
        <? if ($slot_id): ?>
            <?= _('Anmerkung zu diesem Sprechstundentermin bearbeiten') ?>
        <? else: ?>
            <?= _('Anmerkung zu diesem Sprechstundenblock bearbeiten') ?>
        <? endif; ?>
        </legend>

        <label>
            <?=_('Anmerkung') ?>
            <textarea name="note"><?= htmlReady($slot_id ? $block->slots->find($slot_id)->note : $block->note ) ?></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for("consultation/admin#block-{$block->id}")
        ) ?>
    </footer>
</form>
