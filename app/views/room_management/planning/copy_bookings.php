<? if ($step == 1): ?>
    <? if ($rooms): ?>
        <form class="default" method="post" data-dialog="size=auto"
              action="<?= $controller->link_for('room_management/planning/copy_bookings')?>">
            <?= CSRFProtection::tokenTag() ?>
            <fieldset>
                <legend><?= _('Verfügbare Räume') ?></legend>
                <table class="default">
                    <colgroup>
                        <col class="checkbox">
                        <col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox"
                                       data-proxyfor="input[name='selected_room_ids[]']">
                            </th>
                            <th><?= _('Name')?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <? foreach ($rooms as $room): ?>
                            <tr>
                                <td>
                                    <input type="checkbox"
                                           name="selected_room_ids[]"
                                           value="<?= htmlReady($room->id) ?>"
                                           <?= in_array($room->id, $selected_room_ids)
                                             ? 'checked="checked"'
                                             : ''?>>
                                </td>
                                <td><?= htmlReady($room->name)?></td>
                            </tr>
                        <? endforeach ?>
                    </tbody>
                </table>
            </fieldset>
            <fieldset>
                <legend><?= _('Semester') ?></legend>
                <label>
                    <?= _('Aus welchem Semester sollen Buchungen kopiert werden?') ?>
                    <select name="source_semester_id">
                        <? foreach ($available_semesters as $semester): ?>
                            <option value="<?= htmlReady($semester->id)?>"
                                    <?= $semester->id == $source_semester_id
                                      ? 'selected="selected"'
                                      : '' ?>>
                                <?= htmlReady($semester->name) ?>
                            </option>
                        <? endforeach ?>
                    </select>
                </label>
                <label>
                    <input type="checkbox" name="sem_week_selected" value="1"
                           data-activates="input[type='number'][name='selected_sem_week']"
                           <?= $sem_week_selected ? 'checked="checked"' : ''?>>
                       <?= _('Buchungen sollen erst ab der folgenden Semesterwoche kopiert werden:') ?>
                       <input type="number" min="1" max="53" name="selected_sem_week"
                              value="<?= htmlReady($selected_sem_week) ?>">
                </label>
            </fieldset>
            <div data-dialog-button="1">
                <?= \Studip\Button::create(
                    _('Auswählen'),
                    'select_rooms'
                ) ?>
            </div>
        </form>
    <? else: ?>
        <?= MessageBox::error(
            _('Die gewählte Raumgruppe ist leer!')
        ) ?>
    <? endif ?>
<? elseif ($step == 2): ?>
    <form class="default" method="post" data-dialog="size=auto"
          action="<?= $controller->link_for('room_management/planning/copy_bookings')?>">
        <?= CSRFProtection::tokenTag() ?>
        <? foreach ($selected_room_ids as $room_id): ?>
            <input type="hidden" name="selected_room_ids[]"
                   value="<?= htmlReady($room_id) ?>">
        <? endforeach ?>
        <input type="hidden" name="source_semester_id"
               value="<?= htmlReady($source_semester_id)?>">
        <input type="hidden" name="sem_week_selected"
               value="<?= htmlReady($sem_week_selected) ?>">
        <input type="hidden" name="selected_sem_week"
               value="<?= htmlReady($selected_sem_week) ?>">
        <fieldset>
            <legend><?= _('Buchungen') ?></legend>
            <table class="default">
                <colgroup>
                    <col class="checkbox">
                    <col>
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox"
                                   data-proxyfor="input[name='selected_booking_ids[]']">
                        </th>
                        <th><?= _('Raum') ?></th>
                        <th><?= _('Zeitbereiche')?></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($bookings as $booking): ?>
                        <tr>
                            <td>
                                <input type="checkbox"
                                       name="selected_booking_ids[]"
                                       value="<?= htmlReady($booking->id) ?>">
                            </td>
                            <td><?= htmlReady($booking->resource->name)?></td>
                            <td>
                                <? if ($booking_time_ranges[$booking->id]): ?>
                                    <ul>
                                        <? foreach ($booking_time_ranges[$booking->id] as $str): ?>
                                            <?= htmlReady($str) ?>
                                        <? endforeach ?>
                                    </ul>
                                <? endif ?>
                            </td>
                        </tr>
                    <? endforeach ?>
                </tbody>
            </table>
        </fieldset>
        <fieldset>
            <legend><?= _('Zielsemester') ?></legend>
            <label>
                <?= _('In welches Semester sollen die Buchungen kopiert werden?') ?>
                <select name="target_semester_id">
                    <? foreach ($available_target_semesters as $semester): ?>
                        <option value="<?= htmlReady($semester->id)?>"
                                <?= $semester->id == $target_semester_id
                                  ? 'selected="selected"'
                                  : '' ?>>
                                <?= htmlReady($semester->name) ?>
                        </option>
                    <? endforeach ?>
                </select>
            </label>
        </fieldset>
        <div data-dialog-button="1">
            <?= \Studip\Button::create(
                _('Zurück'),
                'step1',
                ['data-dialog' => 'size=auto']
            ) ?>
            <?= \Studip\Button::create(
                _('Prüfen'),
                'test_copy'
            ) ?>
        </div>
    </form>
<? elseif ($step == 3): ?>
    <form class="default" method="post"
          action="<?= $controller->link_for('room_management/planning/copy_bookings')?>">
        <?= CSRFProtection::tokenTag() ?>
        <? foreach ($selected_room_ids as $room_id): ?>
            <input type="hidden" name="selected_room_ids[]"
                   value="<?= htmlReady($room_id) ?>">
        <? endforeach ?>
        <input type="hidden" name="source_semester_id"
               value="<?= htmlReady($source_semester_id)?>">
        <input type="hidden" name="sem_week_selected"
               value="<?= htmlReady($sem_week_selected) ?>">
        <input type="hidden" name="selected_sem_week"
               value="<?= htmlReady($selected_sem_week) ?>">
        <? foreach ($selected_booking_ids as $booking_id): ?>
            <input type="hidden" name="selected_booking_ids[]"
                   value="<?= htmlReady($booking_id) ?>">
        <? endforeach ?>
        <input type="hidden" name="target_semester_id"
               value="<?= htmlReady($target_semester_id)?>">

        <fieldset>
            <legend><?= _('Prüfung der Machbarkeit') ?></legend>
            <table class="default">
                <thead>
                    <tr>
                        <th><?= _('Buchungszeitraum') ?></th>
                        <th><?= _('Raum') ?></th>
                        <th><?= _('Verfügbar') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($booking_copy_data as $data): ?>
                        <tr>
                            <td>
                                <? if ($data['time_intervals']): ?>
                                    <ul>
                                        <? foreach ($data['time_intervals'] as $interval): ?>
                                            <li>
                                                <?= htmlReady(date('d.m.Y H:i', $interval['begin'])) ?>
                                                -
                                                <?= htmlReady(date('d.m.Y H:i', $interval['end'])) ?>
                                            </li>
                                        <? endforeach ?>
                                    </ul>
                                <? endif ?>
                            </td>
                            <td>
                                <?= htmlReady($data['original']->resource->name) ?>
                            </td>
                            <td>
                                <?= Icon::create(
                                    (
                                        $data['available']
                                        ? 'accept'
                                        : 'decline'
                                    ),
                                    (
                                        $data['available']
                                        ? 'status-green'
                                        : 'status-red'
                                    )
                                )->asImg('20px', ['class' => 'text-bottom']) ?>
                            </td>
                        </tr>
                    <? endforeach ?>
                </tbody>
            </table>
        </fieldset>
        <div data-dialog-button="1">
            <?= \Studip\Button::create(
                _('Zurück'),
                'step2',
                ['data-dialog' => 'size=normal']
            ) ?>
            <? if ($show_copy_button) : ?>
                <?= \Studip\Button::create(
                    _('Kopieren'),
                    'copy',
                    ['data-dialog' => 'size=normal']
                ) ?>
            <? else : ?>
                <?= \Studip\Button::create(
                    _('Liste mit Buchungen herunterladen'),
                    'download_booking_list'
                ) ?>
            <? endif ?>
        </div>
    </form>
<? endif ?>
