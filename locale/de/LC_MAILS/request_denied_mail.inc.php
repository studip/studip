<? $range_object = $request->getRangeObject(); ?>
Ihre Anfrage vom <?= date('d.m.Y', $request->mkdate) ?>
 <?= $range_object instanceof Course ? sprintf('zur Veranstaltung %s', htmlReady($range_object->name)) : ''?> wurde
 <?= $request->last_modifier instanceof User
   ? 'von ' . $request->last_modifier->getFullName()
 : ''
 ?> abgelehnt.

<? if ($request->reply_comment): ?>
Begründung/Kommentar: <?= $request->reply_comment ?>
<? endif ?>


Die angefragten Zeiträume waren:

<?= implode('', array_map(function($a) {
    return "<br>- " . $a . "<br>";
}, $request->getTimeIntervalStrings()))?>