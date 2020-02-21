<? if ($show_info) : ?>
    <form id="resolve-request" class="default" method="post" data-dialog="size=big"
          action="<?= $controller->link_for('resources/room_request/resolve/' . $request->id) ?>">
        <?= CSRFProtection::tokenTag() ?>
        <article class="studip left-part">
            <header><h1><?= _('Informationen zur Anfrage') ?></h1></header>
            <section>
                <dl>
                    <? if ($request->course): ?>
                        <dt><?= _('Betroffene Veranstaltung') ?></dt>
                        <dd><?= htmlReady($request->course->getFullName()) ?></dd>
                        <? $lecturers = CourseMember::findByCourseAndStatus(
                            $request->course->id,
                            'dozent'
                        ) ?>
                        <dt><?= _('Lehrende') ?></dt>
                        <dd>
                            <? if (count($lecturers) == 1): ?>
                                <?= htmlReady($lecturers[0]->getUserFullname()) ?>
                            <? else: ?>
                                <ul>
                                    <? foreach ($lecturers as $lecturer): ?>
                                        <li><?= htmlReady($lecturer->getUserFullname()) ?></li>
                                    <? endforeach ?>
                                </ul>
                            <? endif ?>
                        </dd>
                    <? endif ?>
                    <dt><?= _('Art der Anfrage') ?></dt>
                    <dd><?= htmlReady($request->getTypeString()) ?></dd>
                    <dt><?= _('Erstellung') ?></dt>
                    <dd><?= htmlReady(
                        sprintf(
                            _('Anfrage erstellt am %1$s von %2$s'),
                            date('d.m.Y H:i', $request->mkdate),
                            ($request->user
                           ? $request->user->getFullName()
                           : '')
                        )
                        ) ?></dd>
                    <dt><?= _('Bearbeitung') ?></dt>
                    <dd><?= htmlReady(
                        sprintf(
                            _('Letzte Änderung am %1$s von %2$s'),
                            date('d.m.Y H:i', $request->chdate),
                            ($request->last_modifier
                           ? $request->last_modifier->getFullName()
                           : '')
                        )
                        ) ?></dd>
                    <dt><?= _('Aktuelle Zahl an teilnehmenden Personen') ?></dt>
                    <dd>
                        <? if ($request->course): ?>
                            <?= htmlReady($request->course->getNumParticipants()) ?>
                        <? else: ?>
                            <?= _('Nicht verfügbar') ?>
                        <? endif ?>
                    </dd>
                    <dt><?= _('Angeforderte Belegungszeiten') ?></dt>
                    <dd>
                        <? $dates = $request->getDateString(true) ?>
                        <? foreach ($dates as $date) : ?>
                            <?= htmlReady($date) ?><br>
                        <? endforeach ?>
                        <? if ($request_semester_string): ?>
                            (<?= htmlReady($request_semester_string) ?>)
                        <? endif ?>
                    </dd>
                    <? if($room_request->preparation_time): ?>
                        <? $preparation_time_minutes = intval(
                            $room_request->preparation_time / 60
                        ) ?>
                        <dt><?= _('Rüstzeit')?></dt>
                        <dd>
                            <?= htmlReady(
                                sprintf(
                                    ngettext(
                                        '%d Minute',
                                        '%d Minuten',
                                        $preparation_time_minutes
                                    ),
                                    $preparation_time_minutes
                                )
                            ) ?>
                        </dd>
                    <? endif ?>
                    <dt><?= _('Gewünschte Raumeigenschaften') ?></dt>
                    <dd>
                        <table>
                            <tbody>
                                <? foreach ($request->properties as $property): ?>
                                    <tr>
                                        <td><?= htmlReady($property->display_name) ?></td>
                                        <td><?= htmlReady($property->__toString()) ?></td>
                                    </tr>
                                <? endforeach ?>
                            </tbody>
                        </table>
                    </dd>
                    <dt><?= _('Gewünschter Raum') ?></dt>
                    <dd>
                        <?= $request->resource
                          ? htmlReady($request->resource->name)
                          : _('Kein Raum ausgewählt') ?>
                    </dd>
                    <dt><?= _('Kommentar des Anfragenden') ?></dt>
                    <dd><?= $request->comment
                          ? htmlReady($request->comment)
                          : _('Es wurde kein Kommentar eingegeben.') ?>
                    </dd>
                    <dt><?= _('Antwort') ?></dt>
                    <? if ($request->closed == 0) : ?>
                        <dd>
                            <textarea name="reply_comment"><?= htmlReady($room_request->reply_comment) ?></textarea>
                        </dd>
                    <? else : ?>
                        <dd><?= htmlReady($request->reply_comment) ?></dd>
                    <? endif ?>
                </dl>
            </section>
        </article>
        <? if ($show_form): ?>
            <article class="right-part">
                <article class="studip">
                    <header><h1><?= _('Auswahl alternative Räume') ?></h1></header>
                    <section>
                        <label>
                            <input type="radio" name="alternatives_selection" value="clipboard"
                                   <?= $alternatives_selection == 'clipboard'
                                     ? 'checked="checked"'
                                     : '' ?>>
                            <?= _('Auswahl anhand einer Raumgruppe') ?>
                            <select name="selected_clipboard_id">
                                <? foreach ($clipboards as $clipboard): ?>
                                <option value="<?= htmlReady($clipboard->id) ?>"
                                        <?= $selected_clipboard_id == $clipboard->id
                                          ? 'selected="selected"'
                                          : ''?>>
                                    <?= htmlReady($clipboard->name) ?>
                                </option>
                            <? endforeach ?>
                            </select>
                        </label>
                        <label>
                            <input type="radio" name="alternatives_selection" value="room_search"
                                   <?= $alternatives_selection == 'room_search'
                                     ? 'checked="checked"'
                                     : '' ?>>
                        <?= _('Raumsuche') ?>
                        <?= $room_search->render() ?>
                        </label>
                        <label>
                            <input type="radio" name="alternatives_selection" value="my_rooms"
                                   <?= $alternatives_selection == 'my_rooms'
                                     ? 'checked="checked"'
                                     : '' ?>>
                            <? if ($user_is_global_autor): ?>
                                <?= _('Alle Räume') ?>
                            <? else: ?>
                                <?= _('Alle meine Räume') ?>
                            <? endif ?>
                        </label>
                            <? if (!$config->RESOURCES_DIRECT_ROOM_REQUESTS_ONLY): ?>
                                <label>
                                    <input type="radio" name="alternatives_selection" value="request"
                                           <?= $alternatives_selection == 'request'
                                             ? 'checked="checked"'
                                             : '' ?>>
                                    <?= _('Suche anhand der gewünschten Raumeigenschaften') ?>
                                </label>
                            <? endif ?>
                        <?= \Studip\Button::create(_('Auswählen'), 'select_alternatives') ?>
                    </section>
                </article>
                <article class="studip notification-settings">
                    <header><h1><?= _('Benachrichtigung über die Auflösung der Anfrage') ?></h1></header>
                    <section>
                        <label>
                            <input type="radio" name="notification_settings" value="creator"
                                   <?= $notification_settings == 'creator'
                                     ? 'checked="checked"'
                                     : ''
                                   ?>>
                        <?= _('Nur die erstellende Person benachrichtigen.') ?>
                        </label>
                        <label>
                            <input type="radio" name="notification_settings" value="creator_and_lecturers"
                                   <?= $notification_settings == 'creator_and_lecturers'
                                     ? 'checked="checked"'
                                     : ''
                                   ?>>
                        <?= _('Die erstellende Person und alle Lehrenden benachrichtigen.') ?>
                        </label>
                    </section>
                </article>
            </article>
            <article class="studip assign-dates">
                <header><h1><?= _('Termine zuordnen') ?></h1></header>
                <table id="resolve-dates-table" class="default">
                    <thead>
                        <tr>
                            <th><?= _('Raum') ?></th>
                            <th><?= _('Alle Termine') ?></th>
                            <? foreach ($request_time_intervals as $metadate_id => $data): ?>
                                <? if ($data['metadate'] instanceof SeminarCycleDate) : ?>
                                <? $date_string = $data['metadate']->toString('short') ?>
                                    <th><?= htmlReady($date_string) ?></th>
                                <? else : ?>
                                    <? foreach ($data['intervals'] as $time_interval) : ?>
                                        <?
                                        $date_string1 = sprintf(
                                            '%1$s., %2$s',
                                            getWeekday(date('w', $time_interval['begin'])),
                                            date('d.m', $time_interval['begin'])
                                        );
                                        $date_string2 = sprintf(
                                            '%1$s - %2$s',
                                            date('H:i', $time_interval['begin']),
                                            date('H:i', $time_interval['end'])
                                        );
                                        ?>
                                        <th><?= htmlReady($date_string1) ?><br><?= htmlReady($date_string2) ?></th>
                                    <? endforeach ?>
                                <? endif ?>
                            <? endforeach ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="nohover">
                            <td><?= _('Keine Auswahl') ?></td>
                            <td>
                                <input type="checkbox" data-proxyfor="input.radio-null"
                                       name="all_in_room" value="">
                            </td>
                            <? foreach ($request_time_intervals as $metadate_id => $data): ?>
                                <? if (($data['metadate'] instanceof SeminarCycleDate)) : ?>
                                    <?
                                    $range_index = 'SeminarCycleDate' . '_' . $metadate_id;
                                    $room_radio_name = 'selected_rooms[' . $range_index . ']';
                                    ?>
                                    <td>
                                        <input type="radio" name="<?= htmlReady($room_radio_name) ?>"
                                               class="text-bottom radio-null"
                                               value=""
                                               <?= $selected_dates[$range_index] == null
                                                 ? 'checked="checked"'
                                                 : ''?>>
                                    </td>
                                <? else : ?>
                                    <? $i = 0 ?>
                                    <? foreach($data['intervals'] as $interval) : ?>
                                        <?
                                        $range_index = $interval['range'] . '_' . $interval['range_id'];
                                        $room_radio_name = 'selected_rooms[' . $range_index . ']';
                                        ?>
                                        <td>
                                            <input type="radio" name="<?= htmlReady($room_radio_name) ?>"
                                                   class="radio-null text-bottom"
                                                   value=""
                                                   <?= $selected_dates[$range_index] == null
                                                     ? 'checked="checked"'
                                                     : ''?>>
                                        </td>
                                    <? $i++ ?>
                                    <? endforeach ?>
                                <? endif ?>
                            <? endforeach ?>
                        </tr>
                        <? if ($request_resource instanceof Room): ?>
                            <?= $this->render_partial(
                                'resources/room_request/resolve_room_tr.php',
                                [
                                    'room' => $request_resource,
                                    'time_intervals' => $request_time_intervals,
                                    'availability' => $room_availability[$request_resource->id],
                                    'underload' => $room_underload[$room_request->resource_id],
                                    'selected_dates' => $selected_rooms
                                ]
                            ) ?>
                        <? endif ?>
                        <? if ($alternative_rooms): ?>
                            <? foreach ($alternative_rooms as $room): ?>
                                <?= $this->render_partial(
                                    'resources/room_request/resolve_room_tr.php',
                                    [
                                        'room' => $room,
                                        'time_intervals' => $request_time_intervals,
                                        'availability' => $this->room_availability[$room->id],
                                        'underload' => $room_underload[$room->id],
                                        'selected_dates' => $selected_rooms
                                    ]
                                ) ?>
                            <? endforeach ?>
                        <? endif ?>
                    </tbody>
                </table>
            </article>
            <div data-dialog-button>
                <? if ($show_force_resolve_button): ?>
                    <?= \Studip\Button::create(_('Anfrage trotzdem auflösen'), 'force_resolve') ?>
                <? else: ?>
                    <?= \Studip\Button::create(_('Anfrage auflösen'), 'resolve') ?>
                <? endif ?>
                <? if ($request->isSimpleRequest()
                       && !$request->isReadOnlyForUser($current_user)): ?>
                    <?= \Studip\LinkButton::create(
                        _('Anfrage bearbeiten'),
                        URLHelper::getURL(
                            'dispatch.php/resources/room_request/edit/' . $request->id
                        ),
                        ['data-dialog' => 'size=auto']
                    ) ?>
                <? elseif ($GLOBALS['perm']->have_studip_perm('tutor', $request->getRangeId())): ?>
                    <?= \Studip\LinkButton::create(
                        _('Anfrage bearbeiten'),
                        URLHelper::getURL(
                            'dispatch.php/course/room_requests/request_summary/' . $request->id,
                            ['cid' => $request->getRangeId()]
                        )
                    ) ?>
                <? endif ?>
                <?= \Studip\LinkButton::create(
                    _('Anfrage ablehnen'),
                    URLHelper::getURL(
                        'dispatch.php/resources/room_request/decline/' . $request->id
                    ),
                    ['data-dialog' => 'size=auto']
                ) ?>
                <?= \Studip\LinkButton::create(
                    _('Anfrage löschen'),
                    URLHelper::getURL(
                        'dispatch.php/resources/room_request/decline/' . $request->id,
                        ['delete' => '1']
                    ),
                    ['data-dialog' => 'size=auto']
                ) ?>
                <? if ($show_expand_metadates_button) : ?>
                    <?= \Studip\Button::create(_('Terminserien expandieren'), 'expand_metadates') ?>
                <? endif ?>
                <? if (Request::submitted('expand_metadates')) : ?>
                    <?= \Studip\Button::create(
                        _('Terminserien zusammenklappen'),
                        'fold_metadates'
                    ) ?>
                <? endif ?>
            </div>
        <? endif ?>
    </form>
<? endif ?>
