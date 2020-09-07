<? if ($resource) : ?>
    <? if (Request::get("allday")) {
        $min_time = '00:00:00';
        $max_time = '24:00:00';
    } else {
        $min_time = Config::get()->RESOURCES_BOOKING_PLAN_START_HOUR . ':00';
        $max_time = Config::get()->RESOURCES_BOOKING_PLAN_END_HOUR . ':00';
    } ?>

    <? if ($resource instanceof Room) : ?>
        <? if (Request::isDialog()) : ?>
            <section class="fullcalendar-dialogwidget-container">
                <div class="fullcalendar-dialogwidget-widget">
                    <div class="fullcalendar-dialogwidget-widget-header">
                    <?= _('Semester auswählen') ?>
                    </div>
                    <div class="fullcalendar-dialogwidget-widget-content">
                        <form action="<?= $plan_link ?>" method="get" data-dialog="size=big">
                            <input type="hidden" name="resource_id"
                                   value="<?= htmlReady($resource->id) ?>">
                            <input type="hidden" value="0" name="allday">
                            <select class="fullcalendar-dialogwidget-selectlist submit-upon-select"
                                    name="semester_id">
                                <? foreach ($dialog_semesters as $sem) : ?>
                                    <option value="<?= htmlReady($sem->id) ?>"
                                            title="<?= htmlReady($sem->name) ?>"
                                            <?= ($current_semester_id == $sem->id)
                                              ? 'selected="selected"' : '' ?>>
                                        <?= htmlReady($sem->name) ?>
                                    </option>
                                <? endforeach ?>
                            </select>
                            <noscript>
                                <button type="submit" class="button"
                                        name="Zuweisen"><?= _('Zuweisen') ?></button>
                            </noscript>
                        </form>
                    </div>
                </div>
            </section>
        <? endif ?>

        <section class="studip-fullcalendar-header <?= Request::isDialog()?'fullcalendar-dialog':'';?>"
                 data-semester-begin="" data-semester-end="">
            <div id="booking-plan-header-resource-name-line">
                <? if ($resource instanceof Room) : ?>
                    <?= htmlReady($resource->name) ?>,
                    <?= htmlReady(sprintf(_('%d Sitzplätze'), $resource->seats)) ?>,
                <? else : ?>
                    <?= htmlReady($resource->name) ?>
                <? endif ?>
                <span id="booking-plan-header-semrow">
                    <strong>
                        <?= _('Semester') ?>
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
    <? endif ?>
    <?= \Studip\Fullcalendar::create(
        _('Semesterplan'),
        [
            'editable' => true,
            'selectable' => ($fullcalendar_studip_urls['add'] != null),
            'studip_urls' => $fullcalendar_studip_urls,
            'minTime' => ($min_time),
            'maxTime' => ($max_time),
            'allDaySlot' => false,
            'columnHeaderFormat' => ['weekday'=> 'short'],
            'header' => [
                'left' => '',
                'right' => ''
            ],
            'defaultView' =>
                in_array(Request::get("defaultView"), ['dayGridMonth','timeGridWeek','timeGridDay'])
                ? Request::get("defaultView")
                : 'timeGridWeek',
            'defaultDate' => ((Request::get("semester_timerange") == 'fullsem') ? date('Y-m-d',$semester->beginn) : date('Y-m-d',$semester->vorles_beginn)),
            'eventSources' => [
                [
                    'url' => URLHelper::getURL(
                        sprintf(
                            'api.php/resources/resource/%s/semester_plan',
                            htmlReady($resource->id)
                        )
                    ),
                    'method' => 'GET',
                    'extraParams' => [
                        'booking_types' => [0,1,2],
                        'semester_id' => $semester->id,
                        'semester_timerange' => Request::get("semester_timerange", 'vorles'),
                        'display_requests' => 1,
                        'display_all_requests' => $display_all_requests ? 1 : 0
                    ]
                ]
            ],
            'nowIndicator' => false
        ],
        ['class' => 'resource-plan semester-plan'],
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
<? elseif ($rooms): ?>
    <?= $this->render_partial(
        'resources/_common/_grouped_room_list.php',
        [
            'grouped_rooms' => RoomManager::groupRooms($rooms),
            'link_template' => $selection_link_template
        ]
    ) ?>
<? else: ?>
    <?= MessageBox::error(
        _('Es wurde kein Raum ausgewählt und Sie haben keine Berechtigungen an Räumen!')
    ) ?>
<? endif ?>

<div data-dialog-button>
    <?= Request::isDialog()?\Studip\LinkButton::create(_('Drucken'), $sem_url, ['class' => 'print_action']):'' ?>
</div>
