Ihre Reservierung für <?= $resource->name ?> wurde von <?= $booking_user->getFullName() ?> überbucht
und ist damit nicht mehr gültig.

Anbei die Daten zur Reservierung:

<? if ($reservation->booking_user instanceof User): ?>
- Buchende Person: <?= $reservation->booking_user->getFullName() ?>
<? endif ?>

<? if (($reservation->assigned_user instanceof User)
       && $reservation->range_id != $reservation->booking_user_id): ?>
- Belegende Person: <?= $reservation->assigned_user->getFullName() ?>
<? endif ?>


Die reservierten Zeiträume waren:
<? $time_intervals = $reservation->getTimeIntervals() ?>
<? foreach ($time_intervals as $interval): ?>
- <?= date('d.m.Y H:i', $interval->begin) ?> - <?= date('d.m.Y H:i', $interval->end) ?>
<? endforeach ?>


<? if ($reservation->description): ?>
Buchungstext:

<?= $reservation->description ?>
<? endif ?>
