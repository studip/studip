<tr class="nohover">
    <td>
        <?
        $room_tooltip_text = $room->room_type;
        ?>
        <? if ($room->bookingPlanVisibleForUser($current_user)): ?>
            <?
            $booking_plan_params = [];
            if ($time_intervals[0]['begin']) {
                $booking_plan_params = [
                    'defaultDate' => date('Y-m-d', $time_intervals[0]['begin'])
                ];
            }
            ?>
            <a href="<?= $controller->link_for(
                     'resources/room_planning/booking_plan/' . $room->id,
                     $booking_plan_params
                     ) ?>" target="_blank"
               title="<?= _('Zum Belegungsplan') ?>">
                <?= htmlReady($room->name) ?>
            </a>
        <? else: ?>
            <?= htmlReady($room->name) ?>
        <? endif ?>
        <?= tooltipIcon($room_tooltip_text) ?>
        – <?= htmlReady(sprintf('%d Sitzplätze', $room->seats)) ?>
        <? if ($underload) : ?>
            [<?= htmlReady($underload) ?>%]
        <? endif ?>
    </td>
    <td class="<?= $room_fully_available[$room->id] ? '' : 'resolve-date-backlit-red' ?>">
        <input type="radio" data-proxyfor="input.radio-<?= htmlReady($room->id) ?>"
               name="all_in_room" value="<?= htmlReady($room->id) ?>"
                <?= $room_fully_available[$room->id] ? '' : 'disabled="disabled"' ?>>
    </td>
    <? foreach ($time_intervals as $metadate_id => $data): ?>
        <? if (($data['metadate'] instanceof SeminarCycleDate)) : ?>
            <?
            $available = $availability[$metadate_id][0];
            $range_index = 'SeminarCycleDate' . '_' . $metadate_id;
            $room_radio_name = 'selected_rooms[' . $range_index . ']';
            ?>
            <td class="<?= $available?'':'resolve-date-backlit-red';?>">
                <? if ($available): ?>
                    <input type="radio" name="<?= htmlReady($room_radio_name) ?>"
                           class="text-bottom radio-<?= htmlReady($room->id) ?>"
                           value="<?= htmlReady($room->id) ?>"
                           <?= $selected_dates[$range_index] == $room->id
                             ? 'checked="checked"'
                             : ''?>>
                <? else: ?>
                    <input type="radio" name="<?= htmlReady($room_radio_name) ?>"
                           value="1" disabled="disabled"
                           class="text-bottom">
                <? endif ?>
            </td>
        <? else : ?>
            <? $i = 0 ?>
            <? foreach($data['intervals'] as $interval) : ?>
                <?
                $available = $availability[$metadate_id][$i];
                $range_index = $interval['range'] . '_' . $interval['range_id'];
                $room_radio_name = 'selected_rooms[' . $range_index . ']';
                ?>
                <td class="<?= $available?'':'resolve-date-backlit-red';?>">
                    <? if ($available): ?>
                        <input type="radio" name="<?= htmlReady($room_radio_name) ?>"
                               class="text-bottom radio-<?= htmlReady($room->id) ?>"
                               value="<?= htmlReady($room->id) ?>"
                               <?= $selected_dates[$range_index] == $room->id
                                 ? 'checked="checked"'
                                 : ''?>>
                    <? else: ?>
                        <input type="radio" name="<?= htmlReady($room_radio_name) ?>"
                               value="1" disabled="disabled"
                               class="text-bottom">
                    <? endif ?>
                </td>
                <? $i++ ?>
            <? endforeach ?>
        <? endif ?>
    <? endforeach ?>
</tr>
