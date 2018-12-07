<? if ($course) : ?>
    <?= $course->dump ?>
<? else : ?>
    <?= MessageBox::error(_('Es wurde keine Veranstaltung ausgewählt!')) ?>
<? endif ?>
