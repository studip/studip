<? if ($bookings): ?>
    <table class="default">
        <caption>
            <? if ($show_all_records): ?>
                <? if ($resource): ?>
                    <?= sprintf(
                        _('%1$s: Alle Buchungen von %2$s'),
                        htmlReady($resource->getFullName()),
                        htmlReady($user->getFullName())
                    ) ?>
                <? else: ?>
                    <?= sprintf(
                        _('Alle Buchungen von %s'),
                        htmlReady($user->getFullName())
                    ) ?>
                <? endif ?>
            <? else: ?>
                <? if ($resource): ?>
                    <?= sprintf(
                        _('%1$s: Aktuelle und zukünftige Buchungen von %2$s'),
                        htmlReady($resource->getFullName()),
                        htmlReady($user->getFullName())
                    ) ?>
                <? else: ?>
                    <?= sprintf(
                        _('Aktuelle und zukünftige Buchungen von %s'),
                        htmlReady($user->getFullName())
                    ) ?>
                <? endif ?>
            <? endif ?>
        </caption>
        <thead>
            <tr>
                <th><?= _('Buchungszeiträume') ?></th>
                <th><?= _('Interner Kommentar') ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($bookings as $booking): ?>
                <tr>
                    <td>
                        <? $intervals = $booking->getTimeIntervals() ?>
                        <? if ($intervals): ?>
                            <ul class="default">
                                <? foreach ($intervals as $interval): ?>
                                    <li>
                                        <?= date('d.m.Y H:i', $interval->begin) ?>
                                        -
                                        <?= date('d.m.Y H:i', $interval->end) ?>
                                    </li>
                                <? endforeach ?>
                            </ul>
                        <? else: ?>
                            <?= date('d.m.Y H:i', $booking->begin) ?>
                            -
                            <?= date('d.m.Y H:i', $booking->end) ?>
                        <? endif ?>
                    </td>
                    <td>
                        <?= htmlReady($booking->internal_comment) ?>
                    </td>
                </tr>
            <? endforeach ?>
        </tbody>
    </table>
<? else: ?>
    <?= MessageBox::info(
        _('Es liegen keine Buchungen vor!')
    ) ?>
<? endif ?>
<div data-dialog-button>
    <? if ($show_all_records): ?>
        <?= \Studip\LinkButton::create(
            _('Nur aktuelle und zukünftige Buchungen anzeigen'),
            URLHelper::getURL(
                'dispatch.php/resources/admin/booking_log/' . $user->id
              . ($resource
               ? '/' . $resource->id
               : ''
              )
            ),
            [
                'data-dialog' => '1'
            ]
        ) ?>
    <? else: ?>
        <?= \Studip\LinkButton::create(
            _('Auch stattgefundene Buchungen anzeigen'),
            URLHelper::getURL(
                'dispatch.php/resources/admin/booking_log/' . $user->id
              . ($resource
               ? '/' . $resource->id
               : ''
              ),
                [
                    'show_all_records' => '1'
                ]
            ),
            [
                'data-dialog' => '1'
            ]
        ) ?>
    <? endif ?>
</div>
