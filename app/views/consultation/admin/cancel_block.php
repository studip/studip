<form action="<?= $controller->cancel_block($block, $page) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Termine absagen') ?></legend>

        <label>
            <?= _('Termin' ) ?><br>
            <ul class="default">
            <? foreach ($block->slots as $slot): ?>
                <? if ($slot->has_bookings): ?>
                    <li>
                        <?= $this->render_partial('consultation/slot-details.php', compact('slot')) ?>
                    </li>
                <? endif; ?>
            <? endforeach; ?>
            </ul>
        </label>

        <label>
            <?= _('Ort') ?><br>
            <?= htmlready($block->room) ?>
        </label>

        <label>
            <?= _('Grund der Absage') ?>
            <textarea name="reason"></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Termine absagen')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->indexURL($page, "#block-{$block->id}")
        ) ?>
    </footer>
</form>
