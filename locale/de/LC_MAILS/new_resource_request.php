<?= $request->user->getFullName() ?> hat eine neue Anfrage
in der Raumverwaltung erstellt.


<? if ($requested_room): ?>
Angefragter Raum: <?= $requested_room ?>
<? elseif ($requested_resource): ?>
Angefragte Ressource: <?= $requested_resource ?>
<? endif ?>


Typ der Anfrage: <?= $request->getTypeString() ?>

<? if (($request->getRangeType() == 'course') && $request->getRangeObject()): ?>
Veranstaltung: <?= $request->getRangeObject()->getFullName() ?>
<? endif ?>

Angefragte Zeiten:

<?= $request->getDateString() ?>
