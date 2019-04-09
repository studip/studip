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
        <? if (Config::get()->RESOURCES_ENABLE && $resList->numberOfRooms()) : ?>
            <label>
                <input style="display: inline;" type="radio" name="room" value="room"
                       id="room" <?= $date->room_assignment->resource_id ? 'checked' : '' ?>>

                <select name="room_sd" style="display: inline-block; width: 50%;" class="single_room">
                    <option value=""><?= _('Wählen Sie einen Raum aus') ?></option>
                    <? foreach ($resList->getRooms() as $room_id => $room) : ?>
                        <option value="<?= $room_id ?>"
                            <?= $date->room_assignment->resource_id == $room_id ? 'selected' : '' ?>>
                            <?= htmlReady($room->getName()) ?>
                            <? if ($room->getSeats() > 1) : ?>
                                <?= sprintf(_('(%d Sitzplätze)'), $room->getSeats()) ?>
                            <? endif ?>
                        </option>
                    <? endforeach ?>
                </select>
                <?= Icon::create('room-clear', 'clickable', ['class' => "bookable_rooms_action", 'title' => _("Nur buchbare Räume anzeigen")]); ?>
            </label>
        <? endif; ?>
        <label class="horizontal">
            <input type="radio" name="room" value="freetext" <?= $date->raum ? 'checked' : '' ?>>
            <input style="display: inline-block; width: 50%;" type="text"
                   name="freeRoomText_sd"
                   placeholder="<?= _('Freie Ortsangabe (keine Raumbuchung)') ?>"
                   value="<?= $date->raum ? htmlReady($date->raum) : '' ?>">
        </label>

        <label>
            <input type="radio" name="room" value="noroom"
                <?= (!empty($date->room_assignment->resource_id) || !empty($date->raum) ? '' : 'checked') ?>>
            <span style="display: inline-block;"><?= _('Kein Raum') ?></span>
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
            <?= Studip\LinkButton::create(_('Raumanfrage erstellen'),
                                          $controller->url_for('course/room_requests/edit/' . $course->id,
                                                               array_merge($params, ['origin' => 'course_timesrooms'])),
                                          ['data-dialog' => 'size=big']) ?>
        <? endif ?>
    </footer>
</form>
