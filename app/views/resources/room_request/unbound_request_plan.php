<? if ($room): ?>
    <? if (Request::get("allday")) {
        $min_time = '00:00:00';
        $max_time = '24:00:00';
    } else {
        $min_time = Config::get()->RESOURCES_BOOKING_PLAN_START_HOUR . ':00';
        $max_time = Config::get()->RESOURCES_BOOKING_PLAN_END_HOUR . ':00';
    } ?>
    <? if ($resource instanceof Room): ?>
        <section class="fullcalendar-header booking-plan-header" style="font-weight: bold; text-align: center;"
                 data-semester-begin="" data-semester-end="">
            <div id="booking-plan-header-calweek-line">
                <?= _('Kalenderwoche') ?>
                <span id="booking-plan-header-calweek"></span>,
                (<span id="booking-plan-header-calbegin"></span>
                -
                <span id="booking-plan-header-calend"></span>)
            </div>
            <div id="booking-plan-header-semrow-line">
                <span id="booking-plan-header-semrow">
                    <?= _('Semester')?>
                    <span id="booking-plan-header-semname"></span>
                    <span id="booking-plan-header-semweek-part">,
                        <?= _('Vorlesungswoche') ?>
                        <span id="booking-plan-header-semweek"></span>
                    </span>
                </span>
            </div>
            <div id="booking-plan-header-resource-name-line">
                <?= htmlReady($room->getFullName()) ?>
            </div>
            <div id="booking-plan-header-seats-line">
                <?= htmlReady(sprintf(_('%d Sitzplätze'), $room->seats)) ?>
            </div>
            <? if ($room->getProperty('room_administrator')): ?>
                <div id="booking-plan-header-room_administrator-line">
                    <a href="<?= $room->getProperty('room_administrator');?>">
                        <?= Icon::create('person', Icon::ROLE_CLICKABLE, ['class' => 'text-bottom']); ?>
                        <?= $room->getPropertyObject('room_administrator')->display_name; ?>
                    </a>
                </div>
            <? endif; ?>
            <? if ($room->getProperty('administration_url')): ?>
                <div id="booking-plan-header-administration_url-line">
                    <a href="<?= $room->getProperty('administration_url');?>">
                        <?= Icon::create('link-extern', Icon::ROLE_CLICKABLE, ['class' => 'text-bottom']); ?>
                        <?= $room->getPropertyObject('administration_url')->display_name; ?>
                    </a>
                </div>
            <? endif; ?>
        </section>
    <? endif ?>
    <?= \Studip\Fullcalendar::create(
        _('Belegungsplan'),
        [
            'minTime' => ($min_time),
            'maxTime' => ($max_time),
            'allDaySlot' => false,
            'header' => [
                'left' => 'dayGridMonth,timeGridWeek,timeGridDay',
                'right' => 'prev,next'
            ],
            'defaultView' =>
                in_array(Request::get("defaultView"), ['dayGridMonth','timeGridWeek','timeGridDay'])
                ? Request::get("defaultView")
                : 'timeGridWeek',
            'defaultDate' => Request::get("defaultDate"),
            'eventSources' => [
                [
                    'url' => URLHelper::getURL(
                        'api.php/resources/resource/'
                      . $room->id . '/booking_plan'
                    ),
                    'method' => 'GET',
                    'extraParams' => [
                        'booking_types' => [0,1,2],
                        'display_requests' => 1,
                        'display_all_requests' => 1,
                        'additional_objects' => [
                            'ResourceRequest' => $additional_requests
                        ],
                        'additional_object_colours' => [
                            'ResourceRequest' => [
                                'bg' => '#ff8800',
                                'fg' => '#ffffff'

                            ]
                        ]
                    ]
                ]
            ]
        ],
        ['class' => 'resource-plan']
    ) ?>
    <p>
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
                <?= htmlReady(_('Wiederholungstermin')) ?>
            </li>
            <li class="map-key">
                <?= Icon::create('chat2', Icon::ROLE_INFO, ['class' => 'text-bottom']); ?>
                <?= htmlReady(_('Kommentar')) ?>
            </li>
        </ul>
    </p>
    <? if ($unbound_requests): ?>
        <form class="default" method="post"
              action="<?= $controller->link_for(
                      'resources/room_request/unbound_request_plan',
                      [
                          'resource_id' => $room->id
                      ]
                      )?>">
            <?= CSRFProtection::tokenTag() ?>
            <table class="default">
                <caption><?= _('Ungebundene Anfragen') ?></caption>
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox"
                                   data-proxyfor="input[name='selected_requests[]']">
                        </th>
                        <th><?= _('Veranstaltung') ?></th>
                        <th><?= _('Zeiträume') ?></th>
                        <th><?= _('Gewünschte Eigenschaften') ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="4">
                            <?= \Studip\Button::create(
                                _('Anzeigen'),
                                'display'
                            ) ?>
                            <?= \Studip\Button::create(
                                _('Buchen'),
                                'book'
                            ) ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                   <? foreach ($unbound_requests as $request): ?>
                       <tr>
                           <td>
                               <input type="checkbox" name="selected_requests[]"
                                      value="<?= htmlReady($request->id) ?>">
                           </td>
                           <td><?= htmlReady($request->course->name) ?></td>
                           <td>
                               <? $time_intervals = $request->getTimeIntervalStrings() ?>
                               <? if ($time_intervals): ?>
                                   <ul>
                                       <? foreach ($time_intervals as $interval): ?>
                                           <li><?= htmlReady($interval) ?></li>
                                       <? endforeach ?>
                                   </ul>
                               <? endif ?>
                           </td>
                           <td>
                               <? if ($request->properties): ?>
                                   <ul>
                                       <? foreach ($request->properties as $property): ?>
                                           <li>
                                               <?= htmlReady($property->definition) ?>:
                                               <?= htmlReady($property->state)?>
                                           </li>
                                       <? endforeach ?>
                                   </ul>
                               <? endif ?>
                           </td>
                       </tr>
                   <? endforeach ?>
                </tbody>
            </table>
        </form>
    <? endif ?>
<? else: ?>
    <? if ($rooms): ?>
        <?= $this->render_partial(
            'resources/room_planning/_resource_selection.php',
            [
                'title' => _('Wählbare Räume'),
                'resources' => $rooms,
                'link_template' => $selection_link_template
            ]
        ) ?>
    <? else: ?>
        <?= MessageBox::error(
            _('Es wurde kein Raum ausgewählt und Sie haben keine Berechtigungen an Räumen!')
        ) ?>
    <? endif ?>
<? endif ?>
