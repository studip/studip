<?= $request->user->getFullName() ?> created a new request
in the room management system.


<? if ($requested_room): ?>
Requested room: <?= $requested_room ?>
<? elseif ($requested_resource): ?>
Requested resource: <?= $requested_resource ?>
<? endif ?>


Request type: <?= $request->getTypeString() ?>

<? if ($request->getRangeType() == 'course'): ?>
Course: <?= $request->getRangeObject()->getFullName() ?>
<? endif ?>

Requested time intervals:

<?= $request->getDateString() ?>
