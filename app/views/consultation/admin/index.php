<? if (count($blocks) === 0): ?>

<?= MessageBox::info(sprintf(
    implode('<br>', [
        _('Derzeit sind keine Sprechstundentermine eingetragen.'),
        '<a href="%s" class="button" data-dialog="size=auto">%s</a>',
    ]),
    $controller->create(),
    _('Sprechstundenblöcke anlegen')
))->hideClose() ?>

<? else: ?>

<form action="<?= $controller->bulk($page) ?>" method="post">
<table class="default consultation-overview">
    <colgroup>
        <col width="24px">
        <col width="10%">
        <col width="10%">
        <col>
        <col width="48px">
    </colgroup>
    <thead>
        <tr>
            <th>
                <input type="checkbox" id="checkbox-proxy"
                       class="studip-checkbox"
                       data-proxyfor=".consultation-overview tbody :checkbox"
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
    <tbody id="block-<?= htmlReady($block->id) ?>" <? if ($block->is_expired) echo 'class="block-is-expired"'; ?>>
        <tr>
            <th>
                <input type="checkbox" name="block-id[]" id="slots-<?= htmLReady($block->id) ?>"
                       class="studip-checkbox"
                       value="<?= htmlReady($block->id) ?>"
                       data-proxyfor="#block-<?= htmlReady($block->id) ?> :checkbox[name^=slot]"
                       <? if ($block->has_bookings && !$block->is_expired) echo 'disabled'; ?>>
                <label for="slots-<?= htmlReady($block->id) ?>"></label>
            </th>
            <th colspan="3">
                <?= $this->render_partial('consultation/block-description.php', compact('block')) ?>
            </th>
            <th class="actions">
                <?= ActionMenu::get()->addLink(
                    $controller->edit_roomURL($block),
                    _('Raum bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                )->addLink(
                    $controller->noteURL($block),
                    _('Anmerkung bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                )->addLink(
                    $controller->url_for("consultation/export/print/{$block->id}"),
                    _('Druckansicht anzeigen'),
                    Icon::create('print'),
                    ['target' => '_blank']
                )->condition($block->has_bookings && !$block->is_expired)->addLink(
                    $controller->cancel_blockURL($block),
                    _('Sprechstundentermine absagen'),
                    Icon::create('consultation+remove'),
                    ['data-dialog' => 'size=auto']
                )->condition(!$block->has_bookings || $block->is_expired)->addButton(
                    'remove',
                    _('Sprechstundentermine entfernen'),
                    Icon::create('trash'),
                    [
                        'formaction'   => $controller->removeURL($block),
                        'data-confirm' => _('Wollen Sie diese Sprechtstundentermine wirklich löschen?'),
                    ]
                ) ?>
            </th>
        </tr>
    <? foreach ($block->slots as $slot): ?>
        <tr <? if ($slot->is_expired) echo 'class="slot-is-expired"'; ?>>
            <td>
                <input type="checkbox" name="slot-id[]" id="slot-<?= htmLReady($slot->id) ?>"
                       class="studip-checkbox"
                       value="<?= htmlReady($block->id) ?>-<?= htmlReady($slot->id) ?>"
                       <? if (count($slot->bookings) > 0 && !$slot->is_expired) echo 'disabled'; ?>>
                <label for="slot-<?= htmlReady($slot->id) ?>"></label>
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
                    <?= _('Anmerkung') ?>:
                    <?= htmlReady($slot->note) ?>
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
                    $controller->noteURL($block, $slot),
                    _('Anmerkung bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                )->condition(count($slot->bookings) < $slot->block->size)->addLink(
                    $controller->bookURL($block, $slot),
                    _('Sprechstundentermin reservieren'),
                    Icon::create('consultation+add'),
                    ['data-dialog' => 'size=auto']
                )->condition($slot->has_bookings)->addLink(
                    $controller->reasonURL($block, $slot, $slot->bookings->first()),
                    _('Grund bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                )->condition($slot->has_bookings && !$slot->is_expired)->addLink(
                    $controller->cancel_slotURL($block, $slot),
                    _('Sprechstundentermin absagen'),
                    Icon::create('consultation+remove'),
                    ['data-dialog' => 'size=auto']
                )->condition(!$slot->has_bookings || $slot->is_expired)->addButton(
                    'delete',
                    _('Sprechstundentermin entfernen'),
                    Icon::create('trash'),
                    [
                        'formaction'   => $controller->removeURL($block, $slot),
                        'data-confirm' => _('Wollen Sie diesen Sprechstundentermin wirklich entfernen?'),
                    ]
                ) ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
    <tfoot>
        <tr>
            <td colspan="3">
                <?= Studip\Button::create(_('Löschen'), 'delete', [
                    'data-confirm' => _('Wollen Sie diese Sprechtstundentermine wirklich löschen?'),
                ]) ?>
            </td>
            <td colspan="2" class="actions">
                <?= $GLOBALS['template_factory']->render('shared/pagechooser.php', [
                    'num_postings' => $count,
                    'perPage'      => $limit,
                    'page'         => $page,
                    'pagelink'     => str_replace('§u', '%u', str_replace('%', '%%', $controller->indexURL('§u'))),
                ]) ?>
            </td>
        </tr>
    </tfoot>
</table>
</form>

<? endif; ?>
