<form method="get" class="default" action="<?= $controller->planning() ?>">
    <fieldset>
        <legend><?= _('Raumauswahl') ?></legend>

        <select name="room_id" aria-labelledby="<?= _('Bitte wählen Sie einen Raum aus') ?>" onchange="this.form.submit()">
            <option value=""><?= _('Bitte wählen') ?></option>
            <? foreach ($this->available_rooms as $room) : ?>
                <option value="<?= $room->id ?>" <?= $resource && $resource->id === $room->id ? 'selected' : '' ?>>
                    <?= htmlReady($room->name) ?>
                </option>
            <? endforeach ?>
        </select>
    </fieldset>
</form>
<? if ($resource): ?>
    <?
    $min_time = Config::get()->INSTITUTE_COURSE_PLAN_START_HOUR . ':00';
    $max_time = Config::get()->INSTITUTE_COURSE_PLAN_END_HOUR . ':00';
    $default_date = (Request::get("semester_timerange") == 'fullsem') ? $semester->beginn : $semester->vorles_beginn;
    ?>
    <? if ($resource instanceof Room) : ?>
        <section class="studip-fullcalendar-header <?= Request::isDialog() ? 'fullcalendar-dialog' : ''; ?>"
                 data-semester-begin="" data-semester-end="">
            <div id="booking-plan-header-resource-name-line">
                <? if ($resource instanceof Room) : ?>
                    <?= htmlReady($resource->name) ?>,
                    <?= htmlReady(sprintf(_('%d Sitzplätze'), $resource->seats)) ?>
                <? else : ?>
                    <?= htmlReady($resource->name) ?>
                <? endif ?>
                <span id="booking-plan-header-semrow">,
                    <strong>
                        <?= _('Semester') ?>
                        <span id="booking-plan-header-semname"><?= htmlReady($semester->name) ?></span>
                        <span id="booking-plan-header-semspan">
                            <? if (Request::get("semester_timerange") == 'fullsem') : ?>
                                <?= sprintf('(%1$s - %2$s)', date('d.m.Y', $semester->beginn), date('d.m.Y', $semester->ende)); ?>
                            <? else : ?>
                                <?= sprintf('(%1$s - %2$s)', date('d.m.Y', $semester->vorles_beginn), date('d.m.Y', $semester->vorles_ende)); ?>
                            <? endif ?>
                        </span>
                    </strong>
                </span>
            </div>
            <? if ($resource->getProperty('room_administrator')): ?>
                <div id="booking-plan-header-room_administrator-line">
                    <a href="<?= $resource->getProperty('room_administrator'); ?>">
                        <?= Icon::create('person', Icon::ROLE_CLICKABLE, ['class' => 'text-bottom']); ?>
                        <?= $resource->getPropertyObject('room_administrator')->display_name; ?>
                    </a>
                </div>
            <? endif; ?>
            <? if ($resource->getProperty('administration_url')): ?>
                <div id="booking-plan-header-administration_url-line">
                    <a href="<?= $resource->getProperty('administration_url'); ?>">
                        <?= Icon::create('link-extern', Icon::ROLE_CLICKABLE, ['class' => 'text-bottom']); ?>
                        <?= $resource->getPropertyObject('administration_url')->display_name; ?>
                    </a>
                </div>
            <? endif; ?>
        </section>
    <? endif ?>
    <?= \Studip\Fullcalendar::create(
        _('Semesterplan'),
        [
            'editable'           => true,
            'selectable'         => ($fullcalendar_studip_urls['add'] != null),
            'studip_urls'        => $fullcalendar_studip_urls,
            'minTime'            => ($min_time),
            'maxTime'            => ($max_time),
            'allDaySlot'         => false,
            'columnHeaderFormat' => ['weekday' => 'short'],
            'header'             => [
                'left'  => '',
                'right' => ''
            ],
            'defaultView'        =>
                in_array(Request::get("defaultView"), ['dayGridMonth', 'timeGridWeek', 'timeGridDay'])
                    ? Request::get("defaultView")
                    : 'timeGridWeek',
            'defaultDate'        => date('Y-m-d', $default_date),
            'eventSources'       => [
                [
                    'url'         => URLHelper::getURL(
                        sprintf(
                            'api.php/resources/resource/%s/semester_plan',
                            htmlReady($resource->id)
                        )
                    ),
                    'method'      => 'GET',
                    'extraParams' => [
                        'booking_types'        => [0, 1, 2],
                        'semester_id'          => $semester->id,
                        'semester_timerange'   => Request::get("semester_timerange", 'vorles'),
                        'display_requests'     => 0,
                        'display_all_requests' => $display_all_requests ? 1 : 0
                    ]
                ]
            ],
            'nowIndicator'       => false
        ],
        ['class' => 'request-plan resource-plan semester-plan'],
        'resources-fullcalendar'
    ) ?>
    <ul class="map-key-list">
        <? foreach ($table_keys as $key): ?>
            <li class="map-key">
                <span style="background-color:<?= $key['colour'] ?>">
                    &nbsp;
                </span>
                <?= htmlReady($key['text']) ?>
            </li>
        <? endforeach ?>
        <li class="map-key">
            <?= Icon::create('refresh', Icon::ROLE_INFO, ['class' => 'text-bottom']); ?>
            <?= _('Wiederholungstermin') ?>
        </li>
        <li class="map-key">
            <?= Icon::create('chat2', Icon::ROLE_INFO, ['class' => 'text-bottom']); ?>
            <?= _('Kommentar') ?>
        </li>
    </ul>
    <?= \Studip\Button::create(
        _('Im Plan gezeigte Anfragen buchen'),
        'bulk-book-requests',
        ['disabled' => 'disabled']
    ); ?>
<? else: ?>
    <?= MessageBox::info(_('Es wurde kein Raum ausgewählt!')) ?>
<? endif ?>

<? if ($requests && $resource): ?>
    <form class="default" method="post"
          action="<?= $controller->link_for('room_request/assign') ?>">
        <table id="external-events" class="default request-list">
            <caption><?= _('Anfragen'); ?></caption>
            <thead>
            <tr>
                <th data-sort="text"><?= _('Datum/Uhrzeit') ?></th>
                <th data-sort="text"><?= _('Name') ?></th>
                <th data-sort="text"><?= _('Lehrende Person(en)') ?></th>
                <th data-sort="text"><?= _('Plätze') ?></th>
                <th data-sort="text"><?= _('Gewünschter Raum') ?></th>
                <th data-sort="text"><?= _('Anfragende Person') ?></th>
                <th data-sort="htmldata"><?= _('Art') ?></th>
            </tr>
            </thead>
            <tbody>
            <? $planner_start = new DateTime();
            $planner_start->setTimestamp($default_date); ?>
            <? $request_list = []; ?>

            <? foreach ($requests as $request) {

                $range_object = $request->getRangeObject();
                $intervals = $request->getTimeIntervals();

                if ($request->getGroupedTimeIntervals(true)) {
                    foreach ($request->getGroupedTimeIntervals(true) as $metadate_id => $data) {
                        $timesort = '';
                        if ($data['metadate'] instanceof SeminarCycleDate) {
                            $date_string = $data['metadate']->toString('short');
                            $timesort = $data['metadate']['weekday'] . str_replace(':', '', $data['metadate']['start_time']);

                            $interval_data = [];
                            $interval_data['date'] = $date_string;
                            $interval_data['metadate'] = $data['metadate'];
                            $interval_data['interval'] = '';
                            $interval_data['request'] = $request;
                            $request_list[$timesort][$range_object->id][] = $interval_data;

                        } else {
                            foreach ($data['intervals'] as $time_interval) {
                                $date_string1 = sprintf(
                                    '%1$s. %2$s',
                                    getWeekday(date('w', $time_interval['begin'])),
                                    date('d.m', $time_interval['begin'])
                                );
                                $date_string2 = sprintf(
                                    '%1$s - %2$s',
                                    date('H:i', $time_interval['begin']),
                                    date('H:i', $time_interval['end'])
                                );
                                $date_string = $date_string1 . ', ' . $date_string2;
                                $timesort = date('w', $time_interval['begin']) . date('His', $time_interval['begin']);
                                $interval_data = [];
                                $interval_data['date'] = $date_string;
                                $interval_data['metadate'] = '';
                                $interval_data['interval'] = $time_interval;
                                $interval_data['request'] = $request;
                                $request_list[$timesort][$range_object->id][] = $interval_data;
                            }
                        }
                    }
                }
            } ?>

            <? ksort($request_list); ?>
            <? foreach ($request_list as $sortdate => $daterequests): ?>
                <? foreach ($daterequests as $range_object_id => $requestsintervals): ?>
                    <? foreach ($requestsintervals as $requestsinterval): ?>

                        <? $request = $requestsinterval['request']; ?>
                        <? $range_object = $request->getRangeObject(); ?>
                        <? if ($range_object instanceof Course) {
                            $displayname = htmlReady($range_object->getFullName('number-type-name'));
                        } elseif ($range_object instanceof User) {
                            $displayname = htmlReady($range_object->getFullName('no_title_rev'));
                        } ?>
                        <?
                        $range_str = '';
                        if ($requestsinterval['metadate']) {
                            if ($requestsinterval['metadate'] instanceof SeminarCycleDate) {
                                $range_str = 'SeminarCycleDate_' . $requestsinterval['metadate']->id;
                                $cdates = $requestsinterval['metadate']->getAllDates();
                                if ($cdates[0]) {
                                    $begin = new DateTime();
                                    $begin->setTimestamp($cdates[0]->date);
                                    $end = new DateTime();
                                    $end->setTimestamp($cdates[0]->end_time);
                                }
                            }

                        } else if ($requestsinterval['interval']) {
                            $range_str = $requestsinterval['interval']['range'] . '_' . $requestsinterval['interval']['range_id'];
                            $begin = new DateTime();
                            $begin->setTimestamp($requestsinterval['interval']['begin']);
                            $end = new DateTime();
                            $end->setTimestamp($requestsinterval['interval']['end']);
                        } else {
                            $begin = $request->getStartDate();
                            $end = $request->getEndDate();
                        }
                        $studip_weekday_begin = ($begin->format('w') == 0) ? '7' : $begin->format('w');
                        $studip_weekday_end = ($end->format('w') == 0) ? '7' : $end->format('w');
                        ?>
                        <tr class="fc-request-event"
                            data-event-id="<?= $request->id . '_' . $sortdate ?>"
                            data-event-request="<?= $request->id ?>"
                            data-event-title="<?= htmlReady($displayname) ?>"
                            data-event-begin="<?= $begin->format('Y-m-d') . 'T' . $begin->format('H:i:s') . '+02:00'; ?>"
                            data-event-end="<?= $end->format('Y-m-d') . 'T' . $end->format('H:i:s') . '+02:00'; ?>"
                            data-event-studip_weekday_begin="<?= $studip_weekday_begin; ?>"
                            data-event-studip_weekday_end="<?= $studip_weekday_end; ?>"
                            data-event-color="<?= $event_color; ?>"
                            data-event-drop-url=""
                            data-event-tooltip=""
                            data-event-resource="<?= $resource->id; ?>"
                            data-event-metadate="<?= $range_str; ?>"
                            data-event-view_urls_edit="<?= $controller->url_for(
                                'resources/room_request/resolve/' . $request->id,
                                [
                                    'searched_room_id'       => $resource->id,
                                    'alternatives_selection' => 'room_search',
                                    'selected_rooms'         => [$range_str => $resource->id],
                                    'reload-on-close'        => 1
                                ]); ?>"
                            style="cursor:pointer"
                        >
                            <td>
                                <?= $requestsinterval['date']; ?>
                            </td>
                            <td>
                                <?= $displayname; ?>
                            </td>

                            <td>
                                <? if ($range_object instanceof Course): ?>
                                    <?= htmlReady(
                                        join(', ', $range_object->members->findBy('status', 'dozent')
                                            ->limit(3)->getUserFullname('no_title_rev')
                                        )
                                    ) ?>
                                <? endif ?>
                            </td>
                            <td>
                                <?= $request->getProperty('seats') ?>
                            </td>
                            <td>
                                <?= $request->resource ? htmlReady($request->resource->name) : '' ?>
                            </td>
                            <td>
                                <? if ($request->user instanceof User): ?>
                                    <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $request->user->username]); ?>">
                                        <?= htmlReady($request->user->getFullName('no_title_rev')) ?>
                                    </a>
                                <? else: ?>
                                    <?= _('Unbekannt') ?>
                                <? endif ?>
                            </td>
                            <td>
                                <?= $request->getTypeString() ?>
                            </td>
                        </tr>
                    <? endforeach ?>
                <? endforeach ?>
            <? endforeach ?>
            </tbody>
        </table>
    </form>
<? endif ?>

<? if (!$requests && $resource) : ?>
    <?= MessageBox::info(_('Es sind keine Anfragen vorhanden!')) ?>
<? endif ?>