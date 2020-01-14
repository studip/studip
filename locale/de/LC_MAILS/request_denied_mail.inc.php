Ihre Anfrage vom <?= date('d.m.Y', $request->mkdate) ?> wurde
 <?= $request->last_modifier instanceof User
   ? 'von ' . $request->last_modifier->getFullName()
 : ''
 ?> abgelehnt.

<? if ($request->reply_comment): ?>
Begründung/Kommentar: <?= $request->reply_comment ?>
<? endif ?>


Die angefragten Zeiträume waren:

<? foreach ($request->getTimeIntervalStrings() as $str): ?>
- <?= $str ?>
<? endforeach ?>
