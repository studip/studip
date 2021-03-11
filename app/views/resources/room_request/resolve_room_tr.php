<tr class="nohover">
    <td class="nowrap">
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
        <?= tooltipIcon($room->room_type) ?>
        – <?= htmlReady(sprintf('%d Sitzplätze', $room->seats)) ?>
        <? if ($underload) : ?>
            [<?= htmlReady($underload) ?>%]
        <? endif ?>
    </td>
    <? if (count($time_intervals) > 1) : ?>
        <td>
            <input type="checkbox" data-proxyfor="input.radio-<?= htmlReady($room->id) ?>"
                   name="all_in_room" value="<?= htmlReady($room->id) ?>"
                   <?= $room_availability_share[$room->id] <= 0.0  ? 'disabled="disabled"' : '' ?>>
            <? if ($room_availability_share[$room->id] >= 1.0) : ?>
                <?= Icon::create('check-circle', Icon::ROLE_STATUS_GREEN)->asImg(['class' => 'text-bottom']) ?>
            <? elseif ($room_availability_share[$room->id] <= 0.0) : ?>
                <?= Icon::create('decline-circle', Icon::ROLE_STATUS_RED)->asImg(['class' => 'text-bottom']) ?>
            <? else : ?>
                <?= Icon::create('exclaim-circle', Icon::ROLE_STATUS_YELLOW)->asImg(['class' => 'text-bottom']) ?>
            <? endif ?>
        </td>
    <? endif ?>
    <? foreach ($time_intervals as $metadate_id => $data): ?>
        <? if (($data['metadate'] instanceof SeminarCycleDate)) : ?>
            <?
            $available = $metadate_available[$room->id][$metadate_id];
            $range_index = 'SeminarCycleDate' . '_' . $metadate_id;
            $room_radio_name = 'selected_rooms[' . $range_index . ']';
            ?>
            <td>
                <? if ($available): ?>
                    <input type="radio" name="<?= htmlReady($room_radio_name) ?>"
                           class="text-bottom radio-<?= htmlReady($room->id) ?>"
                           value="<?= htmlReady($room->id) ?>"
                           <?= $selected_dates[$range_index] == $room->id
                             ? 'checked="checked"'
                             : ''?>>
                    <?= Icon::create('check-circle', Icon::ROLE_STATUS_GREEN)->asImg(['class' => 'text-bottom']) ?>
                <? else: ?>
                    <input type="radio" name="<?= htmlReady($room_radio_name) ?>"
                           value="1" disabled="disabled"
                           class="text-bottom">
                    <?= Icon::create('decline-circle', Icon::ROLE_STATUS_RED)->asImg(['class' => 'text-bottom']) ?>
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
                <td>
                    <? if ($available): ?>
                        <input type="radio" name="<?= htmlReady($room_radio_name) ?>"
                               class="text-bottom radio-<?= htmlReady($room->id) ?>"
                               value="<?= htmlReady($room->id) ?>"
                               <?= $selected_dates[$range_index] == $room->id
                                 ? 'checked="checked"'
                                 : ''?>>
                        <?= Icon::create('check-circle', Icon::ROLE_STATUS_GREEN)->asImg(['class' => 'text-bottom']) ?>
                    <? else: ?>
                        <input type="radio" name="<?= htmlReady($room_radio_name) ?>"
                               value="1" disabled="disabled"
                               class="text-bottom">
                        <?= Icon::create('decline-circle', Icon::ROLE_STATUS_RED)->asImg(['class' => 'text-bottom']) ?>
                    <? endif ?>
                </td>
                <? $i++ ?>
            <? endforeach ?>
        <? endif ?>
    <? endforeach ?>
</tr>
