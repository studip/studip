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

<form action="#" method="post">
<table class="default">
    <colgroup>
        <col width="10%">
        <col width="10%">
        <col>
        <col width="48px">
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
    <tbody id="block-<?= htmlReady($block->id) ?>">
        <tr>
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
                )->condition($block->has_bookings)->addLink(
                    $controller->cancel_blockURL($block),
                    _('Sprechstundentermine absagen'),
                    Icon::create('consultation+remove'),
                    ['data-dialog' => 'size=auto']
                )->condition(!$block->has_bookings)->addButton(
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
        <tr>
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
                )->condition(count($slot->bookings) > 0)->addLink(
                    $controller->reasonURL($block, $slot, $slot->bookings->first()),
                    _('Grund bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                )->condition(count($slot->bookings) > 0)->addLink(
                    $controller->cancel_slotURL($block, $slot),
                    _('Sprechstundentermin absagen'),
                    Icon::create('consultation+remove'),
                    ['data-dialog' => 'size=auto']
                )->condition(count($slot->bookings) === 0)->addButton(
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
<? if ($count > $limit): ?>
    <tfoot>
        <tr>
            <td colspan="4">
                <?= $GLOBALS['template_factory']->render('shared/pagechooser.php', [
                    'num_postings' => $count,
                    'perPage'      => $limit,
                    'page'         => $page,
                    'pagelink'     => str_replace('§u', '%u', str_replace('%', '%%', $controller->indexURL('§u'))),
                ]) ?>
            </td>
        </tr>
    </tfoot>
<? endif; ?>
</table>
</form>

<? endif; ?>
