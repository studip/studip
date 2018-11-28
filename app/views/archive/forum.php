<? if ($course) : ?>
<?= $course->forumdump; ?>
<? else : ?>
<?= MessageBox::error(_('Es wurde keine Veranstaltung ausgewählt!')); ?>
<? endif ?>
