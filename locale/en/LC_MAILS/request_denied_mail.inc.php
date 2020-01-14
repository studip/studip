Your request from <?= date('d.m.Y', $request->mkdate) ?> has been denied
 <?= $request->last_modifier instanceof User
   ? 'by ' . $request->last_modifier->getFullName()
 : ''
 ?>.

<? if ($request->reply_comment): ?>
Explanation/Comment: <?= $request->reply_comment ?>
<? endif ?>


The requested time ranges were:

<? foreach ($request->getTimeIntervalStrings() as $str): ?>
- <?= $str ?>
<? endforeach ?>
