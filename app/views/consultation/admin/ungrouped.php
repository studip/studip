<? if (count($blocks) === 0): ?>

<?= MessageBox::info(sprintf(
    implode('<br>', [
        _('Derzeit sind keine Termine eingetragen.'),
        '<a href="%s" class="button" data-dialog="size=auto">%s</a>',
    ]),
    $controller->create(),
    _('Terminblöcke anlegen')
))->hideClose() ?>

<? else: ?>

<form action="<?= $controller->bulk($page, $current_action === 'expired') ?>" method="post">
<table class="default consultation-overview block-overview">
    <caption><?= _('Terminblöcke') ?></caption>
    <colgroup>
        <col style="width: 24px">
        <col>
        <col>
        <col>
        <col>
        <col style="width: 96px">
    </colgroup>
    <thead>
        <tr>
            <th>
                <input type="checkbox" id="checkbox-block-proxy"
                       class="studip-checkbox"
                       data-proxyfor=".block-overview tbody :checkbox"
                       data-activates=".block-overview tfoot button">
                <label for="checkbox-block-proxy"></label>
            </th>
            <th><?= _('Tag') ?></th>
            <th><?= _('Zeit') ?></th>
            <th><?= _('Bei') ?></th>
            <th><?= _('Ort') ?></th>
            <th class="actions"><?= _('Optionen') ?></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($blocks as $block): ?>
        <tr id="block-<?= htmlReady($block->id) ?>" class="<? if ($block->is_expired) echo 'block-is-expired'; ?> <? if ($block->has_bookings) echo 'is-occupied'; ?>">
            <td>
                <input type="checkbox" id="block-checkbox-<?= htmlReady($block->id) ?>"
                       name="block-id[]"
                       class="studip-checkbox" value="<?= htmlReady($block->id) ?>">
                <label for="block-checkbox-<?= htmlReady($block->id) ?>"></label>
            </td>
            <td>
                <?= strftime('%A, %x', $block->start) ?>
            </td>
            <td>
                <?= sprintf(
                    _('%s bis %s Uhr'),
                    date('H:i', $block->start),
                    date('H:i', $block->end)
                ) ?>
            </td>
            <td>
            <? if ($block->teacher): ?>
                <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $block->teacher->username]) ?>">
                    <?= htmlReady($block->teacher->getFullName()) ?>
                </a>
            <? else: ?>
                &ndash;
            <? endif; ?>
            </td>
            <td>
                <?= formatLinks($block->room) ?>
            </td>
            <td class="actions">
                <?= ActionMenu::get()->addLink(
                    $controller->edit_roomURL($block, $page),
                    _('Raum bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                )->addLink(
                    $controller->noteURL($block, 0, $page),
                    _('Information bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                )->addLink(
                    $controller->url_for("consultation/export/print/{$block->id}"),
                    _('Druckansicht anzeigen'),
                    Icon::create('print'),
                    ['target' => '_blank']
                )->condition($block->has_bookings)->addLink(
                    $controller->mailURL($block),
                    _('Nachricht schreiben'),
                    Icon::create('mail'),
                    ['data-dialog' => 'size=50%', 'class' => 'send-mail']
                )->condition($block->has_bookings && !$block->is_expired)->addLink(
                    $controller->cancel_blockURL($block, $page),
                    _('Termine absagen'),
                    Icon::create('consultation+remove'),
                    ['data-dialog' => 'size=auto']
                )->condition(!$block->has_bookings || $block->is_expired)->addButton(
                    'remove',
                    _('Termine entfernen'),
                    Icon::create('trash'),
                    [
                        'formaction'   => $controller->removeURL($block, 0, $page),
                        'data-confirm' => _('Wollen Sie diese Termine wirklich löschen?'),
                    ]
                ) ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6">
                <?= Studip\Button::create(_('Nachricht schreiben'), 'mail', [
                    'data-dialog'              => 'size=50%',
                    'data-activates-condition' => '.block-overview tbody tr.is-occupied:has(:checkbox:checked)',
                    'formaction'               => $controller->mailURL('bulk'),
                ]) ?>
                <?= Studip\Button::create(_('Löschen'), 'delete', [
                    'data-confirm' => _('Wollen Sie diese Termine wirklich löschen?'),
                ]) ?>
            </td>
        </tr>
    </tfoot>
</table>

<table class="default consultation-overview slot-overview">
    <caption><?= _('Termine') ?></caption>
    <colgroup>
        <col width="24px">
        <col width="15%">
        <col>
        <col>
        <col>
        <col>
        <col>
        <col style="width: 96px">
    </colgroup>
    <thead>
        <tr>
            <th>
                <input type="checkbox" id="checkbox-slot-proxy"
                       class="studip-checkbox"
                       data-proxyfor=".slot-overview tbody :checkbox"
                       data-activates=".slot-overview tfoot button">
                <label for="checkbox-slot-proxy"></label>
            </th>
            </th>
            <th><?= _('Tag') ?></th>
            <th><?= _('Zeit') ?></th>
            <th><?= _('Ort') ?></th>
            <th><?= _('Status') ?></th>
            <th><?= _('Person(en)') ?></th>
            <th><?= _('Grund') ?></th>
            <th class="actions"><?= _('Optionen') ?></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($slots as $slot): ?>
        <tr id="slot-<?= htmlReady($slot->id) ?>" class="<? if ($slot->is_expired) echo 'slot-is-expired'; ?>  <? if (count($slot->bookings) > 0) echo 'is-occupied'; ?>">
            <td>
                <input type="checkbox" name="slot-id[]" id="slot-<?= htmlReady($slot->id) ?>"
                       class="studip-checkbox"
                       value="<?= htmlReady($slot->block_id) ?>-<?= htmlReady($slot->id) ?>">
                <label for="slot-<?= htmlReady($slot->id) ?>"></label>
            </td>
            <td>
                <?= strftime(_('%A, %x'), $slot->start_time) ?>
            </td>
            <td>
                <?= strftime('%H:%M', $slot->start_time) ?>
                -
                <?= strftime('%H:%M', $slot->end_time) ?>
            <? if ($slot->note): ?>
                <div style="color: black; font-size: 12px" title="<?= htmlReady($slot->note) ?>">
                <? if (mb_strlen($slot->note) > 29): ?>
                    <?= htmlReady(mb_substr($slot->note, 0, 30)) ?>&hellip;
                <? else: ?>
                    <?= htmlReady($slot->note) ?>
                <? endif; ?>
                </div>
            <? endif; ?>
            </td>
            <td>
                <?= htmlReady($block->room) ?>
            </td>
            <td>
                <?= $this->render_partial('consultation/slot-occupation.php', compact('slot')) ?>
            </td>
            <td>
            <? if (count($slot->bookings) === 0): ?>
                &ndash;
            <? else: ?>
                <ul class="default">
                <? foreach ($slot->bookings as $booking): ?>
                    <li>
                        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $booking->user->username]) ?>">
                            <?= htmlReady($booking->user->getFullName()) ?>
                        </a>
                    </li>
                <? endforeach; ?>
                </ul>
            <? endif; ?>
            </td>
            <td>
            <? if (count($slot->bookings) === 0): ?>
                &ndash;
            <? else: ?>
                <ul class="default">
                <? foreach ($slot->bookings as $booking): ?>
                    <li>
                        <?= htmlReady($booking->reason ?: _('Kein Grund angegeben')) ?>
                    </li>
                <? endforeach; ?>
                </ul>
            <? endif; ?>
            </td>
            <td class="actions">
                <?= ActionMenu::get()->addLink(
                    $controller->noteURL($slot->block, $slot, $page),
                    _('Information bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                )->condition(!$slot->is_expired && count($slot->bookings) < $slot->block->size)->addLink(
                    $controller->bookURL($slot->block, $slot, $page),
                    _('Termin reservieren'),
                    Icon::create('consultation+add'),
                    ['data-dialog' => 'size=auto']
                )->condition($slot->has_bookings)->addLink(
                    $controller->reasonURL($slot->block, $slot, $slot->bookings->first(), $page),
                    _('Grund bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                )->condition($slot->has_bookings)->addLink(
                    $controller->mailURL($slot->block, $slot),
                    _('Nachricht schreiben'),
                    Icon::create('mail'),
                    ['data-dialog' => 'size=50%', 'class' => 'send-mail']
                )->condition($slot->has_bookings && !$slot->is_expired)->addLink(
                    $controller->cancel_slotURL($slot->block, $slot, $page),
                    _('Termin absagen'),
                    Icon::create('consultation+remove'),
                    ['data-dialog' => 'size=auto']
                )->condition(!$slot->has_bookings || $slot->is_expired)->addButton(
                    'delete',
                    _('Termin entfernen'),
                    Icon::create('trash'),
                    [
                        'formaction'   => $controller->removeURL($slot->block, $slot, $page),
                        'data-confirm' => _('Wollen Sie diesen Termin wirklich entfernen?'),
                    ]
                ) ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="8">
                <?= Studip\Button::create(_('Nachricht schreiben'), 'mail', [
                    'data-dialog'              => 'size=50%',
                    'data-activates-condition' => '.slot-overview tbody tr.is-occupied:has(:checkbox:checked)',
                    'formaction'               => $controller->mailURL('bulk'),
                ]) ?>
                <?= Studip\Button::create(_('Löschen'), 'delete', [
                    'data-confirm'             => _('Wollen Sie diese Termine wirklich löschen?'),
                    'data-activates-condition' => '.slot-overview tbody tr:not(.is-occupied):has(:checkbox:checked)',
                ]) ?>

                <div class="actions">
                    <?= Pagination::create($count, $page, $limit)->asLinks(function ($page) use ($controller, $current_action) {
                        return $controller->action_link($current_action, $page);
                    }) ?>
                </div>
            </td>
        </tr>
    </tfoot>
</table>
</form>

<? endif; ?>
