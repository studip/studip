<? if (!$locked) : ?>
    <form action="<?= $controller->url_for('course/study_areas/save/' . $course->id, $url_params) ?>" method="post">
<? endif?>
    <?= $tree ?>
    <div style="text-align: center;">
    <? if ($must_have_studyareas) : ?>
        <?= _("Die Veranstaltung muss <b>mindestens einen</b> Studienbereich haben.") ?>
    <? else : ?>
        <?= _("Die Veranstaltung darf <b>keine</b> Studienbereiche haben.") ?>
    <? endif ?>
    </div>
<? if(!$locked) : ?>
    </form>
<? endif ?>
