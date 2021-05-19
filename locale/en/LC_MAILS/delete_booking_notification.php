<? if ($resource instanceof Room): ?>
Your booking of the room <?= $resource->name ?> on <?= date('d.m.Y', $begin) ?>
 from <?= date('H:i', $begin) ?> to <?= date('H:i', $end) ?> has been deleted.
<? else: ?>
Your booking of the resource <?= $resource->name ?> on <?= date('d.m.Y', $begin) ?>
 from <?= date('H:i', $begin) ?> to <?= date('H:i', $end) ?> has been deleted.
<? endif ?>

<? if ($deleting_user instanceof User) : ?>
The deletion has been made by <?= $deleting_user->getFullName() ?>.
<? endif ?>
