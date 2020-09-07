<? if (Request::get("allday")) {
        $min_time = '00:00:00';
        $max_time = '24:00:00';
    } else {
        $min_time = Config::get()->RESOURCES_BOOKING_PLAN_START_HOUR . ':00';
        $max_time = Config::get()->RESOURCES_BOOKING_PLAN_END_HOUR . ':00';
    } ?>
<?= \Studip\Fullcalendar::create(
    _('Belegungsplan'),
    [
        'minTime' => ($min_time),
        'maxTime' => ($max_time),
        'allDaySlot' => false,
        'defaultView' =>
            in_array(Request::get("defaultView"), ['dayGridMonth','timeGridWeek','timeGridDay'])
            ? Request::get("defaultView")
            : 'timeGridWeek',
        'defaultDate' => Request::get("defaultDate"),
        'eventSources' => [
            [
                'url' => URLHelper::getLink(
                    'api.php/resources/resource/'
                  . htmlReady($resource->id) . '/booking_plan'
                ),
                'method' => 'GET',
                'extraParams' => [
                    'booking_types' => [0,1,2],
                    'display_requests' => 0
                ]
            ]
        ]
    ],
    [],
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
        <li class="map-key">
            <?= Icon::create('refresh', Icon::ROLE_INFO, ['class' => 'text-bottom']); ?>
            <?= _('Wiederholungstermin') ?>
        </li>
        <li class="map-key">
            <?= Icon::create('chat2', Icon::ROLE_INFO, ['class' => 'text-bottom']); ?>
            <?= _('Kommentar') ?>
        </li>
    <? endforeach ?>
</ul>
