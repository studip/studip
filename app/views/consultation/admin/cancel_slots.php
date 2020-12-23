<form action="<?= $action ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <?= addHiddenFields('slot-id', $slots->map(function ($slot) {
        return "{$slot->block_id}-{$slot->id}";
    })) ?>

    <fieldset>
        <legend><?= _('Termine absagen') ?></legend>

    <? if ($allow_delete): ?>
        <p>
            <?= _('Die folgenden Termine sind belegt und müssen abgesagt werden bevor sie gelöscht werden können.') ?>
        <? if ($mixed): ?>
            <?= _('Alternativ können Sie auch nur die freien Termine löschen.') ?>
        <? endif; ?>
        </p>
    <? endif; ?>

        <label>
            <?= _('Termin') ?><br>
            <ul class="default">
            <? foreach ($slots as $slot): ?>
                <? if ($slot->has_bookings): ?>
                    <li>
                        <?= $this->render_partial('consultation/slot-details.php', compact('slot')) ?>
                    </li>
                <? endif; ?>
            <? endforeach; ?>
            </ul>
        </label>

        <label>
            <?= _('Grund der Absage') ?>
            <textarea name="reason"></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
    <? if ($allow_delete): ?>
        <?= Studip\Button::createAccept(_('Termine absagen und löschen'), 'delete', [
            'value' => 'cancel',
        ]) ?>
        <? if ($mixed): ?>
            <?= Studip\Button::create(_('Nur freie Termine löschen'), 'delete', [
                'value' => 'skip',
            ]) ?>
        <? endif; ?>
    <? else: ?>
        <?= Studip\Button::createAccept(_('Termine absagen')) ?>
    <? endif; ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->indexURL($page, "#block-{$block->id}")
        ) ?>
    </footer>
</form>
