<form action="<?= $controller->book($slot->block, $slot, $page) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

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

        <label>
            <span class="required">
            <? if ($slot->block->range instanceof Course): ?>
                <?= htmlReady(sprintf(
                    _('Teilnehmer der Veranstaltung "%s" suchen'),
                    $slot->block->range->getFullName()
                )) ?>
            <? else: ?>
                <?= _('Person suchen') ?>
            <? endif; ?>
            </span>

            <?= QuickSearch::get('user_id', $search_object)->setAttributes([
                'required' => '',
            ])->withButton() ?>
        </label>

        <label>
            <?= _('Grund') ?>
            <textarea name="reason"></textarea>
        </label>
    </fieldset>


    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Termin reservieren')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->indexURL($page, "#block-{$slot->block_id}")
        ) ?>
    </footer>
</form>
