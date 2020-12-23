<form name="reason_form" action="<?= $controller->book($slot->block, $slot) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

<? if ($slot->block->show_participants): ?>
    <?= MessageBox::info(_('Bitte beachten Sie, dass Ihre Buchung öffentlich sichtbar sein wird'))->hideClose() ?>
<? endif; ?>

    <fieldset>
        <legend><?= _('Termin reservieren') ?></legend>

        <label>
            <?= _('Termin') ?><br>
            <?= $this->render_partial('consultation/slot-details.php', compact('slot')) ?>
        </label>

        <label>
            <?= _('Ort') ?><br>
            <?= htmlready($slot->block->room) ?>
        </label>

    <? if ($slot->block->require_reason !== 'no'): ?>
        <label>
            <span <? if ($slot->block->require_reason === 'yes') echo 'class="required"'; ?>><?= _('Grund') ?></span>
            <textarea name="reason" <? if ($slot->block->require_reason === 'yes') echo 'required'; ?>></textarea>
        </label>
    <? endif; ?>

    <? if ($slot->block->confirmation_text): ?>
        <label>
            <?= _('Bitte lesen Sie sich den folgenden Hinweis durch:') ?>
            <textarea disabled><?= htmlReady($slot->block->confirmation_text) ?></textarea>
        </label>

        <label>
            <input type="checkbox" required>
            <?= _('Ich habe den obigen Hinweis zur Kenntnis genommen') ?>
        </label>
    <? endif; ?>
    </fieldset>


    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Termin reservieren')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->indexURL("#block-{$slot->block_id}")
        ) ?>
    </footer>
</form>
