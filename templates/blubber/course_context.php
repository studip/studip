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
</div>
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