Your reservation for <?= $resource->name ?> has been overbooked by <?= $booking_user->getFullName() ?>
and therefore has become invalid.

Reservation data:

<? if ($reservation->booking_user instanceof User): ?>
- Booking person: <?= $reserveation->booking_user->getFullName() ?>
<? endif ?>

<? if (($reservation->assigned_user instanceof User)
       && $reservation->range_id != $reservation->booking_user_id): ?>
- Assigned person: <?= $reservation->assigned_user->getFullName() ?>
<? endif ?>


The reserved time ranges were:
<? $time_intervals = $reservation->getTimeIntervals() ?>
<? foreach ($time_intervals as $interval): ?>
- <?= date('d.m.Y H:i', $interval->begin) ?> - <?= date('d.m.Y H:i', $interval->end) ?>
<? endforeach ?>


<? if ($reservation->description): ?>
Booking text:

<?= $reservation->description ?>
<? endif ?>
