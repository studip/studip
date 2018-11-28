<? if (!$locked) : ?>
    <form action="<?= $controller->url_for('course/study_areas/save/' . $course->id, $url_params) ?>" method="post">
<? endif?>
    <?= $tree ?>
<? if(!$locked) : ?>
    </form>
<? endif ?>