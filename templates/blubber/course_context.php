<div class="blubber_course_info indented">
    <div class="headline">
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
    <div>
        <? $sem_class = $course->getSemClass() ?>
        <h4><?= htmlReady($sem_class['title_dozent_plural'] ?: $GLOBALS['DEFAULT_TITLE_FOR_STATUS']['dozent'][1]) ?></h4>
        <ol class="clean members">
            <? foreach ($teachers as $teacher) : ?>
                <li>
                    <a href="<?= URLHelper::getLink("dispatch.php/profile", ['username' => $teacher['username']]) ?>">
                        <?= Avatar::getAvatar($teacher['user_id'])->getImageTag(Avatar::SMALL) ?>
                        <?= htmlReady($teacher->getUserFullname()) ?>
                    </a>
                </li>
            <? endforeach ?>
        </ol>
        <? if (count($tutors)) : ?>
            <h4><?= htmlReady($sem_class['title_tutor_plural'] ?: $GLOBALS['DEFAULT_TITLE_FOR_STATUS']['tutor'][1]) ?></h4>
            <ol class="clean members">
                <? foreach ($tutors as $tutor) : ?>
                    <li>
                        <a href="<?= URLHelper::getLink("dispatch.php/profile", ['username' => $tutor['username']]) ?>">
                            <?= Avatar::getAvatar($tutor['user_id'])->getImageTag(Avatar::SMALL) ?>
                            <?= htmlReady($tutor->getUserFullname()) ?>
                        </a>
                    </li>
                <? endforeach ?>
            </ol>
        <? endif ?>
        <h4>
            <?= sprintf(_("%s %s und %s"), $students_count, $sem_class['title_tutor_plural'] ?: $GLOBALS['DEFAULT_TITLE_FOR_STATUS']['autor'][1], $GLOBALS['DEFAULT_TITLE_FOR_STATUS']['user'][1]) ?>
        </h4>
    </div>
</div>
<?= $this->render_partial("blubber/_tagcloud") ?>
<? if (!$GLOBALS['perm']->have_perm("admin")) : ?>
    <div class="indented new_section">
        <a href="#"
           onClick="STUDIP.Blubber.followunfollow.call(this); return false;"
           class="followunfollow<?= $unfollowed ? " unfollowed" : "" ?>"
           title="<?= _("Benachrichtigungen für diese Konversation abstellen.") ?>"
           data-thread_id="<?= htmlReady($thread->getId()) ?>">
            <?= Icon::create("rss+remove", "clickable")->asImg(20, ['class' => "follow text-bottom"]) ?>
            <?= Icon::create("rss", "clickable")->asImg(20, ['class' => "unfollow text-bottom"]) ?>
            <?= _("Benachrichtigungen aktiviert") ?>
        </a>
    </div>
<? endif ?>