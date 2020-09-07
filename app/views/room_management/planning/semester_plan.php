<? if ($no_clipboard): ?>
    <?= MessageBox::info(
        _('Es wurde keine Raumgruppe ausgewählt!')
    ) ?>
<? elseif ($no_rooms): ?>
    <?= MessageBox::info(
        _('Die gewählte Raumgruppe enthält keine Räume!')
    ) ?>
<? else: ?>
    <? if (Request::get("allday")) {
        $min_time = '00:00:00';
        $max_time = '24:00:00';
    } else {
        $min_time = Config::get()->RESOURCES_BOOKING_PLAN_START_HOUR . ':00';
        $max_time = Config::get()->RESOURCES_BOOKING_PLAN_END_HOUR . ':00';
    } ?>

    <section class="studip-fullcalendar-header"
             data-semester-begin="" data-semester-end="">
        <div>
            <?= _('Raumgruppe') ?>
            <span id="booking-plan-header-roomgroup"><?= htmlReady($clipboard->name) ?>,</span>
            <span id="booking-plan-header-semrow">
                <strong>
                    <?= _('Semester')?>
                    <span id="booking-plan-header-semname"><?= htmlReady($semester->name) ?></span>
                    <span id="booking-plan-header-semspan">
                        <? if (Request::get("semester_timerange") == 'fullsem') : ?>
                            <?= sprintf('(%1$s - %2$s)', date('d.m.Y',$semester->beginn), date('d.m.Y', $semester->ende)); ?>
                        <? else : ?>
                            <?= sprintf('(%1$s - %2$s)', date('d.m.Y',$semester->vorles_beginn), date('d.m.Y', $semester->vorles_ende)); ?>
                        <? endif ?>
                    </span>
                </strong>
            </span>
        </div>
    </section>

    <?= \Studip\Fullcalendar::create(
        _('Semesterplan'),
        [
            'resources' => $scheduler_resources,
            'resourceLabelText' => _('Raum'),
            'minTime' => ($min_time),
            'maxTime' => ($max_time),
            'allDaySlot' => false,
            'slotLabelFormat' => [
                ['weekday'=> 'short'], // top level of text
                ['hour'=> '2-digit',
                 'hour12' => false]        // lower level of text
            ],
            'header' => [
                'left' => '',
                'right' => ''
            ],
            'defaultView' =>
                in_array(Request::get("defaultView"), ['resourceTimelineMonth', 'resourceTimelineWeek', 'resourceTimelineDay'])
                         ? Request::get("defaultView")
                         : 'resourceTimelineWeek',
            'defaultDate' => ((Request::get("semester_timerange") == 'fullsem') ? date('Y-m-d',$semester->beginn) : date('Y-m-d',$semester->vorles_beginn)),
            'eventSources' => [
                [
                    'url' => URLHelper::getLink(
                        'api.php/room_clipboard/'
                      . htmlReady($clipboard->id) . '/semester_plan'
                    ),
                    'method' => 'GET',
                    'extraParams' => [
                        'booking_types' => $booking_types,
                        'semester_id' => $semester->id,
                        'semester_timerange' => Request::get("semester_timerange", 'vorles'),
                        'display_requests' => 1,
                        'display_all_requests' => $display_all_requests ? 1 : 0
                    ]
                ]
            ],
            'nowIndicator' => false
        ],
        ['class' => 'resource-plan semester-plan room-group-booking-plan'],
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
<? endif ?>
