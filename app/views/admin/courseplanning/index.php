<?
    $min_time = Config::get()->INSTITUTE_COURSE_PLAN_START_HOUR . ':00';
    $max_time = Config::get()->INSTITUTE_COURSE_PLAN_END_HOUR . ':00';
?>

<?= Studip\Fullcalendar::create($plan_title, [
    'editable' => true,
    'minTime' => $min_time,
    'maxTime' => $max_time,
    'allDaySlot' => false,
    'nowIndicator' => false,
    'slotDuration' => '01:00:00',
    'slotLabelInterval' => '01:00',
    'slotLabelFormat' => ['hour' => '2-digit', 'minute' => '2-digit'],
    'timeZone' => 'UTC',
    'header' => [
        'left' => '',
        'right' => ''
    ],
    'columnHeaderFormat' => ['weekday' => 'long'],
    'defaultView' => 'timeGridWeek',
    'eventSources' => [compact('events')],
    'slotEventOverlap' => false,
    'displayEventTime' => false,
    'editable' => true,
    'droppable' => true, // this allows things to be dropped onto the calendar
    'actionCalled' => 'index'
], [
    'class' => 'institute-plan'
]) ?>

<br>

<? if (count($eventless_courses)) : ?>
<table class="default" id="external-events">
    <tr>
        <th><?= _('Veranstaltungen ohne Termine') ?></th>
    </tr>
    <tr>
    <? foreach ($eventless_courses as $cid => $cname): ?>
        <td class="fc-event"
            data-event-course="<?= $cid ?>"
            data-event-title="<?= htmlReady($cname) ?>"
            data-event-duration="02:00"
            data-event-drop-url="<?= $controller->link_for('admin/courseplanning/add_event') ?>"
            data-event-tooltip=""
        >
            <?= htmlReady($cname) ?>
        </td>
    <? endforeach; ?>
    </tr>
</table>
<? endif; ?>
