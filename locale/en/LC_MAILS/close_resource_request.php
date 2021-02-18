A request <?= $request->course instanceof Course
            ? 'for the course ' . $request->course->getFullName('number-name')
            : '' ?> has been processed.

<? if ($request->course): ?>

Lecturer(s): <?= $lecturer_names ?>

<? endif ?>

Requested room: <?= $request->resource->name ?>


Booked rooms: <?= $booked_rooms ?>


Request type: <?= $request->getTypeString() ?>


The following time ranges have been booked:
<? foreach ($metadates as $metadate) : ?>

- <?= $metadate->toString('full') ?>
<? endforeach ?>
<? foreach ($single_dates as $date) : ?>

<? if($date instanceof CourseDate) : ?>
- <?= $date->getFullname() ?>
<? else : ?>
- <?= $date->toString('default') ?>
<? endif ?>
<? endforeach ?>
<? foreach ($booked_time_intervals as $interval) : ?>

- <?= $interval ?>
<? endforeach ?>
<? if ($request->comment) : ?>


Request comment:

<?= $request->comment ?>
<? endif ?>
<? if ($request->reply_comment): ?>


Room management comment:

<?= $request->reply_comment ?>
<? endif ?>
