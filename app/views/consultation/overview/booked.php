<? if (count($slots) === 0): ?>

<?= MessageBox::info(_('Sie haben aktuell keine Sprechstunden gebucht.'))->hideClose() ?>

<? else: ?>

<table class="default">
    <colgroup>
        <col width="10%">
        <col width="10%">
        <col>
        <col width="24px">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Uhrzeit') ?></th>
            <th><?= _('Status') ?></th>
            <th>
                <?= _('Informationen') ?> /
                <?= _('Mein Grund der Buchung') ?>:
            </th>
            <th></th>
        </tr>
    </thead>
    <tbody>
<? $last_block = null;
   foreach ($slots as $slot): ?>
    <? if ($slot->block != $last_block): ?>
        <tr id="block-<?= htmlReady($slot->block->id) ?>">
            <th colspan="4">
                <?= $this->render_partial('consultation/block-description.php', ['block' => $slot->block]) ?>
            </th>
        </tr>
    <? endif; ?>
        <tr>
            <td>
                <?= date('H:i', $slot->start_time) ?>
                -
                <?= date('H:i', $slot->end_time) ?>
            </td>
            <td>
                <?= $this->render_partial('consultation/slot-occupation.php', compact('slot')) ?>
            </td>
            <td>
            <? if (!$slot->note && (count($slot->bookings) === 0 || !$slot->isOccupied($GLOBALS['user']->id))): ?>
                &ndash;
            <? else: ?>
                <? if ($slot->note): ?>
                    <?= htmlReady($slot->note) ?>
                    <br>
                <? endif; ?>
                <?= htmlReady($slot->bookings->findOneBy('user_id', $GLOBALS['user']->id)->reason) ?>
            <? endif; ?>
            </td>
            <td class="actions">
                <a href="<?= $controller->cancel($slot->block, $slot, 1) ?>" data-dialog="size=auto">
                    <?= Icon::create('trash')->asImg(tooltip2(_('Sprechstundentermin absagen'))) ?>
                </a>
            </td>
        </tr>
<? $last_block = $slot->block;
   endforeach; ?>
    </tbody>
</table>

<? endif; ?>
