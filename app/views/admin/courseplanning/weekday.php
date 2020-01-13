<?
    $min_time = Config::get()->INSTITUTE_COURSE_PLAN_START_HOUR . ':00';
    $max_time = Config::get()->INSTITUTE_COURSE_PLAN_END_HOUR . ':00';
?>

<? $days = ['Sonntag', 'Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag']; ?>
<div class="calendar-caption">
    <?= $days[$cal_date->format('w')] ?>
    <a href="<?= $controller->link_for('admin/courseplanning/index') ?>">
        (<?= _('zurück zur Übersicht'); ?>)
    </a>
</div>

<?= Studip\Fullcalendar::create($plan_title, [
    'editable' => true,
    'minTime' => $min_time,
    'maxTime' => $max_time,
    'allDaySlot' => false,
    'nowIndicator' => false,
    'header' => [
        'left' => '',
        'right' => ''
    ],
    'slotDuration' => '01:00:00',
    'slotLabelInterval' => '01:00',
    'slotLabelFormat' => ['hour' => '2-digit', 'minute' => '2-digit'],
    'timeZone' => 'UTC',
    'defaultDate' => $cal_date->format('Y-m-d'),
    'defaultView' => 'resourceTimeGridDay',
    'eventSources' => [compact('events')],
    'slotEventOverlap' => false,
    'displayEventTime' => false,
    'editable' => true,
    'droppable' => true, // this allows things to be dropped onto the calendar
    'resources' => $columns,
    'actionCalled' => 'weekday/' . $cal_date->format('w')
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
        <td
            class="fc-event"
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
