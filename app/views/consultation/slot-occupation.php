<? if ($slot->isOccupied()): ?>
    <span class="consultation-occupied">
    <? if ($slot->block->size > 1): ?>
        <?= sprintf(
            _('%u von %u belegt'),
            count($slot->bookings),
            $slot->block->size
        ) ?>
    <? else: ?>
        <?= _('belegt') ?>
    <? endif; ?>
    </span>
<? else: ?>
    <span class="consultation-free">
    <? if ($slot->block->size > 1): ?>
        <?= sprintf(
            _('%u von %u belegt'),
            count($slot->bookings),
            $slot->block->size
        ) ?>
    <? else: ?>
        <?= _('frei') ?>
    <? endif; ?>
    </span>
<? endif; ?>
