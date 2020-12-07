<? if (count($slot->bookings) === 0 || !($slot->block->show_participants || $slot->isOccupied($GLOBALS['user']->id))): ?>
    &ndash;
<? elseif ($slot->block->show_participants): ?>
    <ul class="default">
    <? foreach ($slot->bookings as $booking):
        if (!$slot->block->show_participants && $booking->user_id !== $GLOBALS['user']->id) continue;
    ?>
        <li>
            <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $booking->user->username]) ?>">
                <?= htmlReady($booking->user->getFullName()) ?>
            </a>
        <? if ($booking->user_id === $GLOBALS['user']->id): ?>
            -
            <?= _('Mein Grund der Buchung') ?>:
          <? if ($booking->reason): ?>
            <?= htmlReady($booking->reason) ?>
          <? else: ?>
            <span class="consultation-no-reason">
                <?= _('Kein Grund angegeben') ?>
            </span>
          <? endif; ?>
        <? endif; ?>
        </li>
    <? endforeach; ?>
    </ul>
<? elseif ($slot->isOccupied($GLOBALS['user']->id)): ?>
    <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $GLOBALS['user']->username]) ?>">
        <?= htmlReady($GLOBALS['user']->getFullName()) ?>
    </a>
    <? if (count($slot->bookings) > 1): ?>
        (<?= sprintf(_('+%u weitere'), count($slot->bookings) - 1) ?>)
    <? endif; ?>
<? endif; ?>
