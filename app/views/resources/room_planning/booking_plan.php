<? if ($resource): ?>
    <? if (Request::get("allday")) {
        $min_time = '00:00:00';
        $max_time = '24:00:00';
    } else {
        $min_time = Config::get()->RESOURCES_BOOKING_PLAN_START_HOUR . ':00';
        $max_time = Config::get()->RESOURCES_BOOKING_PLAN_END_HOUR . ':00';
    } ?>

    <section id="booking_plan_header"
             class="studip-fullcalendar-header booking-plan-header"
             data-semester-begin="" data-semester-end="">
            <span id="booking-plan-header-resource-name-line">
                <? if ($resource instanceof Room) : ?>
                    <?= htmlReady($resource->name) ?>
                    <span id="booking-plan-header-seats">
                        <?= htmlReady(sprintf(_('%d Sitzplätze'), $resource->seats)) ?>
                    </span>
                <? else : ?>
                    <?= htmlReady($resource->name) ?>
                <? endif ?>
                <span id="booking-plan-header-semrow">
                    <strong>
                        <span id="booking-plan-header-semname"></span>
                        <span id="booking-plan-header-semweek-part">
                            <span id="booking-plan-header-semweek"></span>
                        </span>
                    </strong>
                </span>
            </span>
            <? if ($resource->getProperty('room_administrator')): ?>
                <div id="booking-plan-header-room_administrator-line">
                    <a href="<?= $resource->getProperty('room_administrator');?>">
                        <?= Icon::create('person', Icon::ROLE_CLICKABLE, ['class' => 'text-bottom']); ?>
                        <?= $resource->getPropertyObject('room_administrator')->display_name; ?>
                    </a>
                </div>
            <? endif; ?>
            <? if ($resource->getProperty('administration_url')): ?>
                <div id="booking-plan-header-administration_url-line">
                    <a href="<?= $resource->getProperty('administration_url');?>">
                        <?= Icon::create('link-extern', Icon::ROLE_CLICKABLE, ['class' => 'text-bottom']); ?>
                        <?= $resource->getPropertyObject('administration_url')->display_name; ?>
                    </a>
                </div>
            <? endif; ?>
        </section>
    <?= \Studip\Fullcalendar::create(
        _('Belegungsplan'),
        [
            'editable' => true,
            'selectable' => ($fullcalendar_studip_urls['add'] != null),
            'studip_urls' => $fullcalendar_studip_urls,
            'minTime' => ($min_time),
            'maxTime' => ($max_time),
            'allDaySlot' => false,
            'header' => [
                'left' => 'dayGridMonth,timeGridWeek,timeGridDay',
                'right' => 'prev,next'
            ],
            'weekNumbers' => true,
            'views' => [
                'dayGridMonth' => [
                    'eventTimeFormat' => ['hour' => 'numeric', 'minute' => '2-digit'],
                    'displayEventEnd' => true
                ],
                'timeGridWeek' => [
                  'columnHeaderFormat' => [ 'weekday' => 'short', 'year' => 'numeric', 'month' => '2-digit', 'day' => '2-digit', 'omitCommas' => true ]
                ],
                'timeGridDay' => [
                    'columnHeaderFormat' => [ 'weekday' => 'long', 'year' => 'numeric', 'month' => '2-digit', 'day' => '2-digit', 'omitCommas' => true ]
                  ]
            ],
            'defaultView' =>
                in_array(Request::get("defaultView"), ['dayGridMonth','timeGridWeek','timeGridDay'])
                ? Request::get("defaultView")
                : 'timeGridWeek',
            'defaultDate' => Request::get("defaultDate"),
            'eventSources' => [
                [
                    'url' => URLHelper::getURL(
                        'api.php/resources/resource/' . $resource->id . '/booking_plan'
                    ),
                    'method' => 'GET',
                    'extraParams' => [
                        'booking_types' => $booking_types,
                        'display_requests' => $anonymous_view ? 0 : 1,
                        'display_all_requests' => $display_all_requests ? 1 : 0
                    ]
                ]
            ]
        ],
        ['class' => 'resource-plan'],
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
<? else: ?>
    <? if ($rooms): ?>
        <?= $this->render_partial(
            'resources/_common/_grouped_room_list.php',
            [
                'grouped_rooms' => RoomManager::groupRooms($rooms),
                'link_template' => $selection_link_template,
                'show_in_dialog' => false
            ]
        ) ?>
    <? else: ?>
        <?= MessageBox::error(
            _('Es wurde kein Raum ausgewählt und Sie haben keine Berechtigungen an Räumen!')
        ) ?>
    <? endif ?>
<? endif ?>
