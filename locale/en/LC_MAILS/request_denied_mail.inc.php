Your room request from <?= date('d.m.Y', $request->mkdate) ?>
 <?= $range_object instanceof Course ? sprintf('for the Course %s', htmlReady($range_object->getFullname())) : ''?> has been denied
 <?= $request->last_modifier instanceof User
   ? 'by ' . $request->last_modifier->getFullName()
 : ''
 ?>.

<? if ($request->reply_comment): ?>
Explanation/Comment: <?= $request->reply_comment ?>
<? endif ?>

The requested time ranges were:

<?= implode('', array_map(function($a) {
    return "- " . $a . "\n";
}, $request->getTimeIntervalStrings()))?>