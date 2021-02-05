<? if ($show_form): ?>
    <form class="default create-booking-form" method="post"
          name="create_booking"
          action="<?= ($booking !== null) && ($mode == 'edit')
              ? $controller->link_for('resources/booking/edit/'. $booking->id)
              : $controller->link_for('resources/booking/add/' . $resource_or_clipboard_id . '/' . $booking_type);
          ?>"
          data-dialog="<?= $no_reload ? 'size=auto' : 'reload-on-close' ?>">
        <input type="hidden" name="origin_url"
               value="<?= htmlReady($origin_url)?>">
        <?= CSRFProtection::tokenTag() ?>
        <? if ($show_booking_type_selection): ?>
            <fieldset class="booking-type-selection">
                <legend><?= _('Bitte wählen Sie einen der folgenden Buchungstypen aus:')?></legend>
                <select name="booking_type">
                    <option value="0"
                        <?= $booking_type == '0'
                            ? 'selected="selected"'
                            : '' ?>>
                        <?= _('Buchung') ?>
                    </option>
                    <option value="1"
                        <?= $booking_type == '1'
                            ? 'selected="selected"'
                            : '' ?>>
                        <?= _('Reservierung') ?>
                    </option>
                    <option value="2"
                        <?= $booking_type == '2'
                            ? 'selected="selected"'
                            : '' ?>>
                        <?= _('Sperrbuchung') ?>
                    </option>
                </select>
            </fieldset>
        <? endif ?>
        <div class="fieldset-row">
            <fieldset class="resource-booking-time-fields">
                <legend><?= _('Zeitbereich') ?></legend>
                <label>
                    <?= _('Uhrzeit (von - bis)') ?>
                    <div class="hgroup">
                        <input type="text" name="begin_time" class="has-time-picker"
                               value="<?= htmlReady($begin->format('H:i')) ?>"
                               pattern="([0-9]|[0-1][0-9]|2[0-3]):[0-5][0-9]"
                               id="BookingStartTimeInput">
                        <input type="text" name="end_time" class="has-time-picker"
                               value="<?= htmlReady($end->format('H:i')) ?>"
                               pattern="([0-9]|[0-1][0-9]|2[0-3]):[0-5][0-9]"
                               id="BookingEndTimeInput">
                    </div>
                </label>

                <label>
                    <?= _('Datum (von - bis)') ?>
                    <div class="indented-form-line manual-time-fields">
                        <? $single_day = ($begin->format('d.m.Y') == $end->format('d.m.Y')); ?>

                        <div id="begin_date-weekdays" class="hgroup">
                            <span id="1" class="<?= $begin->format('N') == 1 ? '' :'invisible'; ?>"><?=  _('Montag'); ?></span>
                            <span id="2" class="<?= $begin->format('N') == 2 ? '' :'invisible'; ?>"><?=  _('Dienstag'); ?></span>
                            <span id="3" class="<?= $begin->format('N') == 3 ? '' :'invisible'; ?>"><?=  _('Mittwoch'); ?></span>
                            <span id="4" class="<?= $begin->format('N') == 4 ? '' :'invisible'; ?>"><?=  _('Donnerstag'); ?></span>
                            <span id="5" class="<?= $begin->format('N') == 5 ? '' :'invisible'; ?>"><?=  _('Freitag'); ?></span>
                            <span id="6" class="<?= $begin->format('N') == 6 ? '' :'invisible'; ?>"><?=  _('Samstag'); ?></span>
                            <span id="7" class="<?= $begin->format('N') == 7 ? '' :'invisible'; ?>"><?=  _('Sonntag'); ?></span>

                            <input type="text" name="begin_date" class="has-date-picker"
                                   value="<?= htmlReady($begin->format('d.m.Y')) ?>"
                                   id="BookingStartDateInput">
                        </div>

                        <div id="end_date-weekdays" class="hgroup" <?= ($single_day)?'style="display:none;"':''; ?>>
                            <span id="1" class="<?= $end->format('N') == 1 ? '' :'invisible'; ?>"><?=  _('Montag'); ?></span>
                            <span id="2" class="<?= $end->format('N') == 2 ? '' :'invisible'; ?>"><?=  _('Dienstag'); ?></span>
                            <span id="3" class="<?= $end->format('N') == 3 ? '' :'invisible'; ?>"><?=  _('Mittwoch'); ?></span>
                            <span id="4" class="<?= $end->format('N') == 4 ? '' :'invisible'; ?>"><?=  _('Donnerstag'); ?></span>
                            <span id="5" class="<?= $end->format('N') == 5 ? '' :'invisible'; ?>"><?=  _('Freitag'); ?></span>
                            <span id="6" class="<?= $end->format('N') == 6 ? '' :'invisible'; ?>"><?=  _('Samstag'); ?></span>
                            <span id="7" class="<?= $end->format('N') == 7 ? '' :'invisible'; ?>"><?=  _('Sonntag'); ?></span>

                            <input type="text" name="end_date" class="has-date-picker"
                                   value="<?= htmlReady($end->format('d.m.Y')) ?>"
                                   id="BookingEndDateInput">
                        </div>
                    </div>

                    <label>
                        <input type="checkbox" id="multiday" <?= $single_day?'':'checked';?>
                               name="multiple_days" value="1"
                               onClick="$('#end_date-weekdays').toggle();">
                        <?= _('Mehrtägig') ?>
                    </label>
                </label>


                <div class="time-option-container invisible">
                    <label>
                        <span id="RepetitionEndLabel"><?= _('Ende der Wiederholung') ?></span>
                        <span id="BlockEndLabel"><?= _('Ende des Terminblocks') ?></span>
                        <input id="RepetitionEndInput" type="text" name="repetition_end" class="has-date-picker"
                               value="<?= $repetition_end->format('d.m.Y') ?>">
                        <input id="HiddenRepetitionEndInput" type="hidden" name="repetition_end"
                               value="<?= $repetition_end->format('d.m.Y') ?>" disabled>
                    </label>

                    <input type="hidden" name="semester_id" value="<?= $semester_id ?>">

                    <label>
                        <input type="radio" name="selected_end" value="manual"
                            <?= (!$semester_id || ($selected_end == 'manual'))
                                ? 'checked="checked"'
                                : '' ?>
                               class="manual-time-option">
                        <?= _('Enddatum manuell festlegen.') ?>
                    </label>
                    <label>
                        <input type="radio" name="selected_end"
                               value="semester_course_end"
                            <?= !$semester_id ? 'disabled="disabled"' : '' ?>
                            <?= ($semester_id && ($selected_end == 'semester_course_end'))
                                ? 'checked="checked"'
                                : '' ?>
                               class="semester-time-option">
                        <input type="hidden" name="semester_course_end_date" value="<?= date('d.m.Y',$semesters[$semester_id]->vorles_ende); ?>">
                        <?= sprintf(
                            _('Ende der Vorlesungszeit des Semesters %s'),
                            sprintf(
                                '<span id="semester_course_name">%s</span>',
                                htmlReady($semesters[$semester_id]->name)
                            )
                        ) ?>
                    </label>
                    <br>
                </div>


                <? if ($booking_type != '2'): ?>
                    <label>
                        <?= _('Rüstzeit (Minuten)') ?>
                        <input type="number" name="preparation_time"
                               value="<?= htmlReady($preparation_time) ?>"
                               min="0"
                               max="<?= htmlReady($max_preparation_time) ?>">
                    </label>
                <? endif ?>

                <span class="notification-span <?= ($booking_type != '2' ) ? 'invisible' : '' ?>" data-booking_type="2">
                    <label>
                        <input type="checkbox" name="notification_enabled"
                               value="1"
                               <?= $notification_enabled
                                   ? 'checked="checked"'
                                   : '' ?>>
                    <?= _('Alle betroffenen Personen über die Sperrbuchung benachrichtigen.') ?>
                    </label>
                </span>

                <? if ($separable_rooms_selected): ?>
                    <label class="separable-room-booking">
                        <input type="checkbox" name="book_other_room_parts"
                               value="1"
                            <?= $book_other_room_parts
                                ? 'checked="checked"'
                                : '' ?>>
                        <span data-booking_type="2"
                              <?= $booking_type == '2' ? '' : 'style="display:none;"'?>>
                            <?= _('Alle anderen Teilräume ebenfalls sperren.') ?>
                        </span>
                        <span data-booking_type="1"
                              <?= $booking_type == '1' ? '' : 'style="display:none;"'?>>
                            <?= _('Alle anderen Teilräume ebenfalls reservieren.') ?>
                        </span>
                        <span data-booking_type="0"
                              <?= (!$booking_type or $booking_type == '0')
                                  ? ''
                                  : 'style="display:none;"'?>>
                            <?= _('Alle anderen Teilräume ebenfalls buchen.') ?>
                        </span>
                    </label>
                <? endif ?>
            </fieldset>

            <section class="fieldset-row inner-row">
                <fieldset id="BookingTypeFieldset">
                    <legend><?= _('Art des Termins') ?></legend>
                    <div class="booking-type-item">
                        <label>
                            <input type="radio" name="booking_style" value="single"
                                <?= empty($block_booking) || $booking_style == 'single'
                                    ? 'checked="checked"'
                                    : '' ?>
                                   class="booking-type-item">
                            <?= _('Einzeltermin') ?>
                        </label>
                        <label  title="<?= _('Konvolut'); ?>">
                            <input type="radio" name="booking_style" value="block"
                                <?= !empty($block_booking) || $booking_style == 'block' ? 'checked="checked"' : '' ?>
                                   class="booking-type-item">
                            <?= _('Terminblock') ?>
                        </label>
                        <label>
                            <input type="radio" name="booking_style" value="repeat"
                                <?= $booking_style == 'repeat' ? 'checked="checked"' : '' ?>
                                   class="booking-type-item">
                            <?= _('Wiederholungstermine') ?>
                        </label>

                    </div>
                </fieldset>

                <fieldset id="BlockBookingFieldset"
                          class="block-booking-item <?= $booking_style == 'block'
                              ? ''
                              : 'invisible' ?>">
                    <legend><?= _('Terminblock') ?></legend>
                    <label>
                        <input type="checkbox" name="block_booking[]"
                               value="each_day"
                            <?= in_array('each_day', $block_booking)
                                ? 'checked="checked"'
                                : '' ?>>
                        <?= _('Jeden Tag') ?>
                    </label>
                    <label>
                        <input type="checkbox" name="block_booking[]"
                               value="mon_fri"
                            <?= in_array('mon_fri', $block_booking)
                                ? 'checked="checked"'
                                : '' ?>>
                        <?= _('Mo-Fr') ?>
                    </label>
                    <label>
                        <input type="checkbox" name="block_booking[]"
                               value="mon"
                            <?= in_array('mon', $block_booking)
                                ? 'checked="checked"'
                                : '' ?>>
                        <?= _('Montags') ?>
                    </label>
                    <label>
                        <input type="checkbox" name="block_booking[]"
                               value="tue"
                            <?= in_array('tue', $block_booking)
                                ? 'checked="checked"'
                                : '' ?>>
                        <?= _('Dienstags') ?>
                    </label>
                    <label>
                        <input type="checkbox" name="block_booking[]"
                               value="wed"
                            <?= in_array('wed', $block_booking)
                                ? 'checked="checked"'
                                : '' ?>>
                        <?= _('Mittwochs') ?>
                    </label>
                    <label>
                        <input type="checkbox" name="block_booking[]"
                               value="thu"
                            <?= in_array('thu', $block_booking)
                                ? 'checked="checked"'
                                : '' ?>>
                        <?= _('Donnerstags') ?>
                    </label>
                    <label>
                        <input type="checkbox" name="block_booking[]"
                               value="fri"
                            <?= in_array('fri', $block_booking)
                                ? 'checked="checked"'
                                : '' ?>>
                        <?= _('Freitags') ?>
                    </label>
                    <label>
                        <input type="checkbox" name="block_booking[]"
                               value="sat"
                            <?= in_array('sat', $block_booking)
                                ? 'checked="checked"'
                                : '' ?>>
                        <?= _('Samstags') ?>
                    </label>
                    <label>
                        <input type="checkbox" name="block_booking[]"
                               value="sun"
                            <?= in_array('sun', $block_booking)
                                ? 'checked="checked"'
                                : '' ?>>
                        <?= _('Sonntags') ?>
                    </label>
                </fieldset>

                <fieldset id="RepetitionBookingFieldset" class="repetition-booking-item
                        <?= $booking_style == 'repeat' ? '' : 'invisible' ?>">
                    <legend><?= _('Wiederholungstermine') ?></legend>

                    <div class="repetition-booking-item">
                        <label>
                            <input type="radio" name="repetition_style" value="daily"
                                <?= $repetition_style == 'daily' ? 'checked="checked"' : '' ?>
                                   class="repetition-booking-item">
                            <?= _('Tägliche Wiederholung') ?>
                        </label>
                        <div class="hgroup indented-form-line">
                            <select name="repetition_interval" id="RepeatIntervalSelectField-Daily">
                                <option value="1"
                                    <?= $repetition_interval == '1'
                                        ? 'selected="selected"'
                                        : ''?>>
                                    <?= _('jeden Tag') ?>
                                </option>
                                <option value="2"
                                    <?= $repetition_interval == '2'
                                        ? 'selected="selected"'
                                        : ''?>>
                                    <?= _('jeden zweiten Tag') ?>
                                </option>
                                <option value="3"
                                    <?= $repetition_interval == '3'
                                        ? 'selected="selected"'
                                        : ''?>>
                                    <?= _('jeden dritten Tag') ?>
                                </option>
                                <option value="4"
                                    <?= $repetition_interval == '4'
                                        ? 'selected="selected"'
                                        : ''?>>
                                    <?= _('jeden vierten Tag') ?>
                                </option>
                                <option value="5"
                                    <?= $repetition_interval == '5'
                                        ? 'selected="selected"'
                                        : ''?>>
                                    <?= _('jeden fünften Tag') ?>
                                </option>
                                <option value="6"
                                    <?= $repetition_interval == '6'
                                        ? 'selected="selected"'
                                        : ''?>>
                                    <?= _('jeden sechsten Tag') ?>
                                </option>
                            </select>
                        </div>
                        <label>
                            <input type="radio" name="repetition_style" value="weekly"
                                <?= $repetition_style == 'weekly' ? 'checked="checked"' : '' ?>
                                   class="repetition-booking-item">
                            <?= _('Wöchentliche Wiederholung') ?>
                        </label>
                        <div class="hgroup indented-form-line">
                            <select name="repetition_interval" id="RepeatIntervalSelectField-Weekly">
                                <option value="1"
                                    <?= $repetition_interval == '1'
                                        ? 'selected="selected"'
                                        : ''?>>
                                    <?= _('jede Woche') ?>
                                </option>
                                <option value="2"
                                    <?= $repetition_interval == '2'
                                        ? 'selected="selected"'
                                        : ''?>>
                                    <?= _('jede zweite Woche') ?>
                                </option>
                                <option value="3"
                                    <?= $repetition_interval == '3'
                                        ? 'selected="selected"'
                                        : ''?>>
                                    <?= _('jede dritte Woche') ?>
                                </option>
                                <option value="4"
                                    <?= $repetition_interval == '4'
                                        ? 'selected="selected"'
                                        : ''?>>
                                    <?= _('jede vierte Woche') ?>
                                </option>
                                <option value="5"
                                    <?= $repetition_interval == '5'
                                        ? 'selected="selected"'
                                        : ''?>>
                                    <?= _('jede fünfte Woche') ?>
                                </option>
                            </select>
                        </div>
                        <label>
                            <input type="radio" name="repetition_style" value="monthly"
                                <?= $repetition_style =='monthly' ? 'checked="checked"' : '' ?>
                                   class="repetition-booking-item">
                            <?= _('Monatliche Wiederholung') ?>
                        </label>
                    </div>
                </fieldset>
            </section>
        </div>
        <div class="fieldset-row">
            <fieldset>
                <legend><?= _('Personen') ?></legend>
                <? if ($booking->assigned_user instanceof User): ?>
                    <p>
                        <a href="<?= $controller->link_for(
                            'profile',
                            ['username' => $booking->assigned_user->username]
                        ) ?>" target="_blank">
                            <?= htmlReady($booking->assigned_user->getFullName()) ?>
                        </a>
                        <a href="<?= $controller->link_for(
                            'messages/write',
                            ['rec_uname' => $booking->assigned_user->username]
                        ) ?>" data-dialog="size=auto">
                            <?= Icon::create('mail')->asImg(
                                '20px',
                                ['class' => 'text-bottom']
                            ) ?>
                        </a>
                        <input type="hidden" name="assigned_user_id"
                               value="<?= htmlReady($booking->range_id) ?>">
                    </p>
                    <label class="assigned-user-label">
                        <?= _('Eine andere nutzende Person auswählen') ?>
                        <div class="assigned-user-search-wrapper">
                            <?= $assigned_user_search->render() ?>
                            <?= Icon::create('refresh')->asImg(
                                '20px', ['class' => 'delete-assigned-user-icon']
                            ) ?>
                        </div>
                    </label>
                <? else: ?>
                    <label class="assigned-user-label">
                        <?= _('Die nutzende Person zur Buchung') ?>
                        <div class="assigned-user-search-wrapper">
                            <?= $assigned_user_search->render() ?>
                            <?= Icon::create('refresh')->asImg(
                                '20px', ['class' => 'delete-assigned-user-icon']
                            ) ?>
                        </div>
                    </label>
                <? endif ?>
                <? if ($booking->booking_user): ?>
                    <p style="margin-top:1em;margin-bottom:0;">
                        <?= htmlReady(
                            _('Gebucht von:')
                        ) ?>
                        <a href="<?= $controller->link_for(
                            'profile',
                            ['username' => $booking->booking_user->username]
                        ) ?>" target="_blank">
                            <?= htmlReady($booking->booking_user->getFullName()) ?>
                        </a>
                        <a href="<?= $controller->link_for(
                            'messages/write',
                            ['rec_uname' => $booking->booking_user->username]
                        ) ?>" data-dialog="size=auto">
                            <?= Icon::create('mail')->asImg(
                                '20px',
                                ['class' => 'text-bottom']
                            ) ?>
                        </a>
                        <? if ($mode == 'edit'): ?>
                            <span style="padding-left: 1em;">
                                <?= sprintf(
                                    _('Letzte Änderung am %s'),
                                    date('d.m.Y', $booking->chdate)
                                ) ?>
                            </span>
                        <? endif ?>
                    </p>
                <? endif ?>
            </fieldset>
            <fieldset class="description">
                <legend><?= _('Buchungstext') ?></legend>
                <label>
                    <textarea name="description"><?= htmlReady($description) ?></textarea>
                </label>
            </fieldset>
        </div>
        <div class="fieldset-row">
            <fieldset class="comment-fieldset">
                <legend data-booking_type="1"
                    <?= $booking_type == '1' ? '' : 'style="display:none;"' ?>>
                    <?= _('Interner Kommentar zur Reservierung') ?>
                </legend>
                <legend data-booking_type="2"
                    <?= $booking_type == '2' ? '' : 'style="display:none;"' ?>>
                    <?= _('Interner Kommentar zur Sperrbuchung') ?>
                </legend>
                <legend data-booking_type="3"
                    <?= $booking_type == '3' ? '' : 'style="display:none;"' ?>>
                    <?= _('Interner Kommentar zur geplanten Buchung') ?>
                </legend>
                <legend data-booking_type="0"
                    <?= (($booking_type == '0') || !$booking_type)
                        ? ''
                        : 'style="display:none;"' ?>>
                    <?= _('Interner Kommentar zur Buchung') ?>
                </legend>
                <label>
                    <textarea name="internal_comment"><?= htmlReady($internal_comment) ?></textarea>
                </label>
            </fieldset>
            <fieldset class="overwrite-fieldset <?= ($booking_type != '2' ) ? 'invisible' : '' ?>" data-booking_type="2">
                <legend><?= _('Vorhandene Buchungen überschreiben') ?></legend>
                <label>
                    <input type="checkbox" value="1"
                           name="overwrite_bookings"
                        <?= $overwrite_bookings ? 'checked="checked"' : '' ?>>
                    <?= _('Vorhandene Buchungen überschreiben') ?>
                </label>
            </fieldset>
            <? if ($booking): ?>
                <? $intervals = $booking->getTimeIntervals(); ?>
                <? if (count($intervals) > 1): ?>
                    <fieldset class="singledates">
                        <legend><?= _('Einzelbuchungen') ?></legend>
                        <? $wdays = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa' ] ?>

                        <table class="default">
                            <? foreach ($intervals as $interval): ?>
                                <tr data-interval_id="<?= htmlReady($interval->id) ?>"
                                    class="booking-list-interval">
                                    <td class="booking-list-interval-date
                                <?= $interval->takes_place ? '': ' not-taking-place' ?>
                                ">
                                        <? if (date('d.m.Y', $interval['begin']) == date('d.m.Y', $interval['end'])): ?>
                                            <?= $wdays[intVal(date('w', $interval['begin']))]
                                            . ' ' . date('d.m.Y, H:i', $interval['begin'])
                                            . date(' - H:i', $interval['end']) ?>
                                        <? else: ?>
                                            <?= $wdays[intVal(date('w', $interval['begin']))]
                                            . ' ' . date('d.m.Y, H:i', $interval['begin']) ?>
                                            -
                                            <?= $wdays[intVal(date('w', $interval['end']))]
                                            . ' ' . date('d.m.Y, H:i', $interval['end']) ?>

                                        <? endif ?>
                                    </td>
                                    <td class="booking-list-interval-actions">
                                        <a class="takes-place-delete takes-place-status-toggle
                                <?= $interval->takes_place ? '': ' invisible'; ?>
                                "
                                           data-interval_id="<?= htmlReady($interval->id) ?>">
                                            <?= Icon::create('trash')->asImg(
                                                [
                                                    'class' => 'text-bottom',
                                                    'title' => _('löschen')
                                                ]
                                            ) ?>
                                        </a>

                                        <a class="takes-place-revive takes-place-status-toggle
                                <?= $interval->takes_place ? ' invisible': ''; ?>
                                "
                                           data-interval_id="<?= htmlReady($interval->id) ?>">
                                            <?= Icon::create('trash+decline')->asImg(
                                                [
                                                    'class' => 'text-bottom',
                                                    'title' => _('wiederherstellen')
                                                ]
                                            ) ?>
                                        </a>
                                    </td>
                                </tr>
                            <? endforeach ?>
                        </table>
                    </fieldset>
                <? endif ?>
            <? endif ?>
        </div>
        <div data-dialog-button>
            <? if ($show_reservation_overwrite_button): ?>
                <?= \Studip\Button::create(
                    _('Reservierungen entfernen und buchen'),
                    'overwrite_and_save'
                ) ?>
            <? else: ?>
                <?= \Studip\Button::create(_('Speichern'), 'save') ?>
            <? endif ?>
        </div>
    </form>
<? endif ?>
