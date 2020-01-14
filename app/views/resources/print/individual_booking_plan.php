<? if (Request::get("allday")) {
        $min_time = '00:00:00';
        $max_time = '24:00:00';
    } else {
        $min_time = Config::get()->RESOURCES_BOOKING_PLAN_START_HOUR . ':00';
        $max_time = Config::get()->RESOURCES_BOOKING_PLAN_END_HOUR . ':00';
    } ?>
<section class="individual-booking-plan">
    <?= \Studip\Fullcalendar::create(
        _('Belegungsplan'),
        [
            'eventSources' => [
                [
                    'url' => URLHelper::getURL(
                        'api.php/resources/resource/' . $resource->id . '/booking_plan'
                    ),
                    'method' => 'GET',
                    'extraParams' => [
                        'booking_types' => [0,1,2]
                    ]
                ]
            ],
            'minTime' => ($min_time),
            'maxTime' => ($max_time),
            'allDaySlot' => false,
            'defaultView' =>
                in_array(Request::get("defaultView"), ['dayGridMonth','timeGridWeek','timeGridDay'])
                ? Request::get("defaultView")
                : 'timeGridWeek',
            'defaultDate' => Request::get("defaultDate"),
            'editable' => false
        ],
        ['class' => 'individual-booking-plan'],
        'resources-fullcalendar'
    ) ?>
</section>
