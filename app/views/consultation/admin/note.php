<? if (!$slot_id): ?>
    <?= MessageBox::info(
        _('Das Ändern der Information wird auch die Information aller Termine dieses Blocks ändern.')
    )->hideClose() ?>
<? endif; ?>

<form action="<?= $controller->note($block, $slot_id ?: 0, $page) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
        <? if ($slot_id): ?>
            <?= _('Information zu diesem Sprechstundentermin bearbeiten') ?>
        <? else: ?>
            <?= _('Information zu diesem Sprechstundenblock bearbeiten') ?>
        <? endif; ?>
        </legend>

        <label>
            <?=_('Information') ?> (<?= _('öffentlich einsehbar') ?>)
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
