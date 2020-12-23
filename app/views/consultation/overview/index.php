<? if (count($blocks) === 0): ?>

<?= MessageBox::info(_('Aktuell werden keine Termine angeboten.'))->hideClose() ?>

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
            <th><?= _('Informationen') ?></th>
            <th></th>
        </tr>
    </thead>
<? foreach ($blocks as $block): ?>
    <tbody>
        <tr id="block-<?= htmlReady($block->id) ?>">
            <th colspan="4">
                <?= $this->render_partial('consultation/block-description.php', compact('block')) ?>
            </th>
        </tr>
    <? foreach ($block->slots as $slot): ?>
        <tr id="<?= htmlReady($slot->id) ?>">
            <td>
                <?= date('H:i', $slot->start_time) ?>
                -
                <?= date('H:i', $slot->end_time) ?>
            </td>
            <td>
                <?= $this->render_partial('consultation/slot-occupation.php', compact('slot')) ?>
            </td>
            <td>
                <?= $displayNote($slot->note, 2048) ?>
                <?= $this->render_partial('consultation/slot-bookings.php', compact('slot')) ?>
            </td>
            <td class="actions">
            <? if ($slot->isOccupied($GLOBALS['user']->id)): ?>
                <a href="<?= $controller->cancel($block, $slot) ?>" data-dialog="size=auto">
                    <?= Icon::create('trash')->asImg(tooltip2(_('Termin absagen'))) ?>
                </a>
            <? elseif (!$slot->isOccupied()): ?>
                <a href="<?= $controller->book($block, $slot) ?>" data-dialog="size=auto">
                    <?= Icon::create('add')->asImg(tooltip2(_('Termin reservieren'))) ?>
                </a>
            <? endif; ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
<? if ($count > $limit): ?>
    <tfoot>
        <tr>
            <td colspan="4">
                <?= Pagination::create($count, $page, $limit)->asLinks(function ($page) use ($controller) {
                    return $controller->index($page);
                }) ?>
            </td>
        </tr>
    </tfoot>
<? endif; ?>
</table>

<? endif; ?>
