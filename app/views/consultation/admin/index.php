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
<table class="default consultation-overview">
    <colgroup>
        <col width="24px">
        <col width="10%">
        <col width="12%">
        <col>
        <col width="48px">
    </colgroup>
    <thead>
        <tr>
            <th>
                <input type="checkbox" id="checkbox-proxy"
                       class="studip-checkbox"
                       data-proxyfor=".consultation-overview tbody th :checkbox"
                       data-activates=".consultation-overview tfoot button">
                <label for="checkbox-proxy"></label>
            </th>
            </th>
            <th><?= _('Uhrzeit') ?></th>
            <th><?= _('Status') ?></th>
            <th><?= _('Informationen') ?></th>
            <th></th>
        </tr>
    </thead>
<? foreach ($blocks as $block): ?>
    <tbody id="block-<?= htmlReady($block['block']->id) ?>" <? if ($block['block']->is_expired) echo 'class="block-is-expired"'; ?>>
        <tr class="<? if ($block['block']->has_bookings) echo 'is-occupied'; ?>">
            <th>
                <input type="checkbox" id="slots-<?= htmLReady($block['block']->id) ?>-checkbox"
                       class="studip-checkbox"
                       data-proxyfor="#block-<?= htmlReady($block['block']->id) ?> :checkbox[name^=slot]">
                <label for="slots-<?= htmlReady($block['block']->id) ?>-checkbox"></label>
            </th>
            <th colspan="3">
                <?= $this->render_partial('consultation/block-description.php', ['block' => $block['block']]) ?>
            </th>
            <th class="actions">
                <?= ActionMenu::get()->addLink(
                    $controller->edit_roomURL($block['block'], $page),
                    _('Raum bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                )->addLink(
                    $controller->noteURL($block['block'], 0, $page),
                    _('Information bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                )->addLink(
                    $controller->url_for("consultation/export/print/{$block['block']->id}"),
                    _('Druckansicht anzeigen'),
                    Icon::create('print'),
                    ['target' => '_blank']
                )->condition($block['block']->has_bookings)->addLink(
                    $controller->mailURL($block['block']),
                    _('Nachricht schreiben'),
                    Icon::create('mail'),
                    ['data-dialog' => 'size=50%', 'class' => 'send-mail']
                )->condition($block['block']->has_bookings && !$block['block']->is_expired)->addLink(
                    $controller->cancel_blockURL($block['block'], $page),
                    _('Termine absagen'),
                    Icon::create('consultation+remove'),
                    ['data-dialog' => 'size=auto']
                )->condition(!$block['block']->has_bookings || $block['block']->is_expired)->addButton(
                    'remove',
                    _('Termine entfernen'),
                    Icon::create('trash'),
                    [
                        'formaction'   => $controller->removeURL($block['block'], 0, $page),
                        'data-confirm' => _('Wollen Sie diese Termine wirklich löschen?'),
                    ]
                ) ?>
            </th>
        </tr>
    <? foreach ($block['slots'] as $slot): ?>
        <tr id="slot-<?= htmlReady($slot->id) ?>" class="<? if ($slot->is_expired) echo 'slot-is-expired'; ?>  <? if (count($slot->bookings) > 0) echo 'is-occupied'; ?>">
            <td>
                <input type="checkbox" name="slot-id[]" id="slot-<?= htmlReady($slot->id) ?>-checkbox"
                       class="studip-checkbox"
                       value="<?= htmlReady($block['block']->id) ?>-<?= htmlReady($slot->id) ?>">
                <label for="slot-<?= htmlReady($slot->id) ?>-checkbox"></label>
            </td>
            <td>
                <?= strftime('%R', $slot->start_time) ?>
                -
                <?= strftime('%R', $slot->end_time) ?>
            </td>
            <td>
                <?= $this->render_partial('consultation/slot-occupation.php', compact('slot')) ?>
            </td>
            <td>
            <? if (!$slot->note && count($slot->bookings) === 0): ?>
                &ndash;
            <? else: ?>
                <? if ($slot->note): ?>
                    <?= formatLinks($slot->note) ?>
                    <br>
                <? endif; ?>
                <? if (count($slot->bookings) > 0): ?>
                    <ul class="default">
                    <? foreach ($slot->bookings as $booking): ?>
                        <li>
                            <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $booking->user->username]) ?>">
                                <?= htmlReady($booking->user->getFullName()) ?>
                            </a>
                            -
                        <? if ($booking->reason): ?>
                            <?= _('Grund') ?>:
                            <?= htmlReady($booking->reason) ?>
                        <? else: ?>
                            <span class="consultation-no-reason">
                                <?= _('Kein Grund angegeben') ?>
                            </span>
                        <? endif; ?>
                        </li>
                    <? endforeach; ?>
                    </ul>
                <? endif; ?>
            <? endif; ?>
            </td>
            <td class="actions">
                <?= ActionMenu::get()->addLink(
                    $controller->noteURL($block['block'], $slot, $page),
                    _('Information bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                )->condition(!$slot->is_expired && count($slot->bookings) < $slot->block->size)->addLink(
                    $controller->bookURL($block['block'], $slot, $page),
                    _('Termin reservieren'),
                    Icon::create('consultation+add'),
                    ['data-dialog' => 'size=auto']
                )->condition($slot->has_bookings)->addLink(
                    $controller->reasonURL($block['block'], $slot, $slot->bookings->first(), $page),
                    _('Grund bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                )->condition($slot->has_bookings)->addLink(
                    $controller->mailURL($block['block'], $slot),
                    _('Nachricht schreiben'),
                    Icon::create('mail'),
                    ['data-dialog' => 'size=50%', 'class' => 'send-mail']
                )->condition($slot->has_bookings && !$slot->is_expired)->addLink(
                    $controller->cancel_slotURL($block['block'], $slot, $page),
                    _('Termin absagen'),
                    Icon::create('consultation+remove'),
                    ['data-dialog' => 'size=auto']
                )->condition(!$slot->has_bookings || $slot->is_expired)->addButton(
                    'delete',
                    _('Termin entfernen'),
                    Icon::create('trash'),
                    [
                        'formaction'   => $controller->removeURL($block['block'], $slot, $page),
                        'data-confirm' => _('Wollen Sie diesen Termin wirklich entfernen?'),
                    ]
                ) ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
    <tfoot>
        <tr>
            <td colspan="5">
                <?= Studip\Button::create(_('Nachricht schreiben'), 'mail', [
                    'data-dialog'              => 'size=50%',
                    'data-activates-condition' => '.consultation-overview tbody tr.is-occupied:has(:checkbox:checked)',
                    'formaction'               => $controller->mailURL('bulk'),
                ]) ?>
                <?= Studip\Button::create(_('Absagen'), 'cancel', [
                    'data-dialog'              => 'size=auto',
                    'data-activates-condition' => '.consultation-overview tbody tr.is-occupied:has(:checkbox:checked)',
                    ]) ?>
                <?= Studip\Button::create(_('Löschen'), 'delete', [
                    'class'        => 'consultation-delete-check',
                    'data-dialog'  => 'size=auto',
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
