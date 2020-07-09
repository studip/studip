<? if (count($overbooked_rooms)): ?>
    <table class="default">
        <thead>
            <tr>
                <th><?= _('Raumname') ?></th>
                <th><?= _('Sitzplätze') ?></th>
                <th><?= _('Anzahl Teilnehmende der Veranstaltung') ?></th>
                <th><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($overbooked_rooms as $room): ?>
            <tr>
                <td><?= htmlReady($room->name) ?></td>
                <td><?= htmlReady($room->seats) ?></td>
                <td>
                    <? if ($courses[$room->id]): ?>
                        <ul>
                        <? foreach ($courses[$room->id] as $course): ?>
                            <li>
                                <?= htmlReady($course['name']) ?>
                                <?= htmlReady(
                                    sprintf(
                                        _('%d Teilnehmende'),
                                        $course['participants']
                                    )
                                ) ?>
                            </li>
                        <? endforeach ?>
                        </ul>
                    <? endif ?>
                </td>
                <td>
                    <?
                    $actions = ActionMenu::get();
                    $actions->addLink(
                        $room->getActionLink('booking_plan'),
                        Icon::create('timetable'),
                        _('Belegungsplan anzeigen'),
                        [
                            'target' => '_blank'
                        ]
                    );
                    ?>
                    <? $actions->render() ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
<? else: ?>
    <?= PageLayout::postInfo(
        _('Es liegen keine überbuchten Räume vor.')
    ) ?>
<? endif ?>
