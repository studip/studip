<div class="blubber_course_info indented">
    <div class="headline">
        <a href="<?= URLHelper::getLink("seminar_main.php", ['auswahl' => $course->getId()]) ?>"
           class="avatar"
           style="background-image: url('<?= CourseAvatar::getAvatar($course->getId())->getURL(Avatar::MEDIUM) ?>');"></a>
        </a>
        <div class="side">
            <a href="<?= URLHelper::getLink("seminar_main.php", ['auswahl' => $course->getId()]) ?>">
                <?= htmlReady($course->name) ?>
            </a>
            <div class="icons">
                <? foreach ($icons as $icon) : ?>
                    <a href="<?= URLHelper::getLink("seminar_main.php", ['auswahl' => $course->getId(), 'redirect_to' => $icon->getURL()]) ?>"<?= $icon->getTitle() ? ' title="'.htmlReady($icon->getTitle()).'"' : "" ?>>
                        <?= $icon->getImageTag() ?>
                    </a>
                <? endforeach ?>
            </div>
        </div>
    </div>
    <? if ($nextdate) : ?>
        <div>
            <h4><?= _("Nächster Termin") ?></h4>
            <a href="<?= URLhelper::getLink("dispatch.php/course/dates/details/".$nextdate->getId()) ?>">
                <?= Icon::create("date", "clickable")->asImg(16, ['class' => "text-bottom"]) ?>
                <?= htmlReady($nextdate->getFullname()) ?>
            </a>
        </div>
    <? endif ?>


    <? if ($thread->isWritable()) : ?>
        <div class="center blubber-edit-icons">
            <form action="<?= URLHelper::getLink("dispatch.php/blubber/delete/".$thread->getId()) ?>"
                  method="post"
                  data-confirm="<?= _('Wirklich löschen?') ?>">
                <?= CSRFProtection::tokenTag() ?>
                <?= Icon::create("trash", "clickable")->asInput(30, ['title' => _('Diesen Blubber löschen.')]) ?>
            </form>
        </div>
    <? endif ?>
</div>
