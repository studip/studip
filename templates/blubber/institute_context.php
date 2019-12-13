<div class="blubber_course_info indented">
    <div class="headline">
        <a href="<?= URLHelper::getLink("dispatch.php/institute/overview", ['auswahl' => $institute->getId()]) ?>"
           class="avatar"
           style="background-image: url('<?= InstituteAvatar::getAvatar($institute->getId())->getURL(Avatar::MEDIUM) ?>');"></a>
        </a>
        <div class="side">
            <a href="<?= URLHelper::getLink("dispatch.php/institute/overview", ['auswahl' => $institute->getId()]) ?>">
                <?= htmlReady($institute->name) ?>
            </a>
        </div>
    </div>
</div>
