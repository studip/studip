<form action="<?= $controller->url_for('course/timesrooms/saveDate/' . $date->termin_id) ?>"
      method="post" class="default collapsable" <?= Request::int('fromDialog') ? 'data-dialog="size=big"' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>
    <fieldset style="margin-top: 1ex">
        <legend><?= _('Zeitangaben') ?></legend>
        <label id="course_type" class=col-6>
            <?= _('Art') ?>
            <select name="course_type" id="course_type" class="size-s">
                <? foreach ($GLOBALS['TERMIN_TYP'] as $id => $value) : ?>
                    <option value="<?= $id ?>"
                        <?= $date->date_typ == $id ? 'selected' : '' ?>>
                        <?= htmlReady($value['name']) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>
        <label class="col-2">
            <?= _('Datum') ?>
            <input class="has-date-picker size-s" type="text" name="date" required
                   value="<?= $date->date ? strftime('%d.%m.%Y', $date->date) : '' ?>">
        </label>
        <label class="col-2">
            <?= _('Startzeit') ?>
            <input class="studip-timepicker size-s" type="text" name="start_time" required placeholder="HH:mm"
                   value="<?= $date->date ? strftime('%H:%M', $date->date) : '' ?>">
        </label>
        <label class="col-2">
            <?= _('Endzeit') ?>
            <input class="studip-timepicker size-s" type="text" name="end_time" required placeholder="HH:mm"
                   value="<?= $date->end_time ? strftime('%H:%M', $date->end_time) : '' ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Raumangaben') ?></legend>
        <? if (Config::get()->RESOURCES_ENABLE
               && ($selectable_rooms || $room_search)): ?>
            <label>
                <input style="display: inline;" type="radio" name="room" value="room"
                       id="room" <?= $date->room_booking->resource_id ? 'checked' : '' ?>
                       data-activates="input.preparation-time[name='preparation_time']">
                <?= _('Raum direkt buchen') ?>
                <span class="flex-row">
                    <? if ($room_search && !$only_bookable_rooms): ?>
                        <?= $room_search
                            ->setAttributes(['onFocus' => "jQuery('input[type=radio][name=room][value=room]').prop('checked', 'checked')"])
                            ->render() ?>
                    <? else: ?>
                        <? $selected_room_id = $date->room_booking->resource_id; ?>
                        <select name="room_id" onFocus="jQuery('input[type=radio][name=room][value=room]').prop('checked', 'checked')">
                            <? foreach ($selectable_rooms as $room): ?>
                                <option value="<?= htmlReady($room->id) ?>"
                                    <?= $selected_room_id == $room->id
                                      ? 'selected="selected"'
                                      : '' ?>>
                                    <?= htmlReady($room->name) ?>
                                    <? if ($room->seats > 1) : ?>
                                        <?= sprintf(_('(%d Sitzplätze)'), $room->seats) ?>
                                    <? endif ?>
                                </option>
                            <? endforeach ?>
                        </select>
                    <? endif ?>
                    <? if (!$only_bookable_rooms) : ?>
                        <a href="<?= $controller->url_for(
                                 'course/timesrooms/editDate/' . $date->termin_id,
                                 ['only_bookable_rooms' => '1']
                                 ) ?>" <?= Request::isDialog() ? 'data-dialog="size=normal"' : '' ?>
                           title="<?= _('Nur buchbare Räume anzeigen') ?>">
                            <?= Icon::create('room-request')->asImg(
                                20,
                                [
                                    'class' => 'text-bottom',
                                    'style' => 'margin-left: 0.2em; margin-top: 0.6em;',
                                ]
                            ) ?>
                        </a>
                    <? endif ?>
                </span>
            </label>
            <label>
                <?= _('Rüstzeit (in Minuten)') ?>
                <input type="number" name="preparation_time"
                       class="preparation-time"
                       value="<?= htmlReady($preparation_time) ?>"
                       min="0" max="<?= htmlReady($max_preparation_time) ?>">
            </label>
        <? endif; ?>
        <label class="horizontal">
            <input type="radio" name="room" value="freetext" <?= $date->raum ? 'checked' : '' ?>
                   data-deactivates="input.preparation-time[name='preparation_time']">
            <?= _('Freie Ortsangabe (keine Raumbuchung)') ?>
            <input type="text"
                   name="freeRoomText_sd"
                   placeholder="<?= _('Freie Ortsangabe (keine Raumbuchung)') ?>"
                   value="<?= $date->raum ? htmlReady($date->raum) : '' ?>">
        </label>

        <label>
            <input type="radio" name="room" value="noroom"
                   <?= (!empty($date->room_booking->resource_id) || !empty($date->raum) ? '' : 'checked') ?>
                   data-deactivates="input.preparation-time[name='preparation_time']">
            <span style="display: inline-block;"><?= _('Kein Raum') ?></span>
        </label>
        <label>
            <input type="radio" name="room" value="nochange" checked="checked"
                   data-deactivates="input.preparation-time[name='preparation_time']">
            <?= _('Keine Änderungen an den Raumangaben vornehmen') ?>
            <? if ($date->room_booking) :?>
                <?=sprintf(_('(gebucht: %s)'), htmlReady($date->room_booking->room_name))?>
            <? endif ?>
        </label>

    </fieldset>

<? if (count($teachers) > 1): ?>
    <fieldset class="collapsed studip-selection" data-attribute-name="assigned_teachers">
        <legend><?= _('Durchführende Lehrende') ?></legend>

        <section class="studip-selection-selected">
            <h2><?= _('Zugewiesene Lehrende') ?></h2>

            <ul>
            <? foreach ($assigned_teachers as $teacher): ?>
                <li data-selection-id="<?= htmlReady($teacher->user_id) ?>">
                    <input type="hidden" name="assigned_teachers[]"
                           value="<?= htmlReady($teacher->user_id) ?>">

                    <span class="studip-selection-image">
                        <?= Avatar::getAvatar($teacher->user_id)->getImageTag(Avatar::SMALL) ?>
                    </span>
                    <span class="studip-selection-label">
                        <?= htmlReady($teacher->getFullname()) ?>
                    </span>
                </li>
            <? endforeach; ?>
                <li class="empty-placeholder">
                    <?= _('Kein spezieller Lehrender zugewiesen') ?>
                </li>
            </ul>
        </section>

        <section class="studip-selection-selectable">
            <h2><?= _('Lehrende der Veranstaltung') ?></h2>

            <ul>
        <? foreach ($teachers as $teacher): ?>
            <? if (!$assigned_teachers->find($teacher->user_id)): ?>
                <li data-selection-id="<?= htmlReady($teacher->user_id) ?>" >
                    <span class="studip-selection-image">
                        <?= Avatar::getAvatar($teacher->user_id)->getImageTag(Avatar::SMALL) ?>
                    </span>
                    <span class="studip-selection-label">
                        <?= htmlReady($teacher->getUserFullname()) ?>
                    </span>
                </li>
            <? endif; ?>
        <? endforeach; ?>
                <li class="empty-placeholder">
                    <?= sprintf(
                            _('Ihre Auswahl entspricht dem Zustand "%s" und wird beim Speichern zurückgesetzt'),
                            _('Kein spezieller Lehrender zugewiesen')
                    ) ?>
                </li>
            </ul>
        </section>
    </fieldset>
<? endif; ?>

<? if (count($groups) > 0): ?>
    <fieldset class="collapsed studip-selection" data-attribute-name="assigned_groups">
        <legend><?= _('Beteiligte Gruppen') ?></legend>

        <section class="studip-selection-selected">
            <h2><?= _('Zugewiesene Gruppen') ?></h2>

            <ul>
            <? foreach ($assigned_groups as $group) : ?>
                <li data-selection-id="<?= htmlReady($group->id) ?>">
                    <input type="hidden" name="assigned_groups[]"
                           value="<?= htmlReady($group->id) ?>">

                    <span class="studip-selection-label">
                        <?= htmlReady($group->name) ?>
                    </span>
                </li>
            <? endforeach ?>
                <li class="empty-placeholder">
                    <?= _('Keine spezielle Gruppe zugewiesen') ?>
                </li>
            </ul>
        </section>

        <section class="studip-selection-selectable">
            <h2><?= _('Gruppen der Veranstaltung') ?></h2>

            <ul>
        <? foreach ($groups as $group): ?>
            <? if (!$assigned_groups->find($group->id)): ?>
                <li data-selection-id="<?= htmlReady($group->id) ?>" >
                    <span class="studip-selection-label">
                        <?= htmlReady($group->name) ?>
                    </span>
                </li>
            <? endif; ?>
        <? endforeach; ?>
                <li class="empty-placeholder">
                    <?= _('Alle Gruppen wurden dem Termin zugewiesen') ?>
                </li>
            </ul>
        </section>
    </fieldset>
<? endif; ?>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save_dates') ?>
        <? if (Request::int('fromDialog')) : ?>
            <?= Studip\LinkButton::create(_('Zurück zur Übersicht'),
                                          $controller->url_for('course/timesrooms',
                                                               ['fromDialog' => 1, 'contentbox_open' => $date->metadate_id]),
                                          ['data-dialog' => 'size=big']) ?>
        <? endif ?>
        <? if (Request::isXhr() && !$locked && Config::get()->RESOURCES_ENABLE && Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS): ?>
            <?  ?>
            <?= Studip\LinkButton::create(
                ($request_id ? _('Zur Raumanfrage wechseln') : _('Raumanfrage erstellen')),
                (
                    $request_id
                    ? $controller->url_for(
                        'course/room_requests/request_summary/' . $request_id
                    )
                    :  $controller->url_for(
                        'course/room_requests/request_start/' . $request_id,
                        array_merge($params, ['range_str' => 'date_' . $date->id,'origin' => 'course_timesrooms'])
                    )
                ),
                ['data-dialog' => 'size=big']) ?>
        <? endif ?>
    </footer>
</form>
