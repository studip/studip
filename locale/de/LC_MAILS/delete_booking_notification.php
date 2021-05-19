<? if ($resource instanceof Room): ?>
Ihre Buchung des Raumes <?= $resource->name ?> am <?= date('d.m.Y', $begin) ?>
 von <?= date('H:i', $begin) ?> bis <?= date('H:i', $end) ?> Uhr wurde gelöscht.
<? else: ?>
Ihre Buchung der Ressource <?= $resource->name ?> am <?= date('d.m.Y', $begin) ?>
 von <?= date('H:i', $begin) ?> bis <?= date('H:i', $end) ?> Uhr wurde gelöscht.
<? endif ?>

<? if ($deleting_user instanceof User) : ?>
Die Löschung wurde von <?= $deleting_user->getFullName() ?> vorgenommen.
<? endif ?>
