<div class="indented">
    <? if (!$thread->statusgruppe) : ?>
        <?= _("Diese Teilnehmendengruppe existiert nicht mehr.") ?>
    <? else : ?>

        <div class="headline">
            <div
               class="avatar"
               style="background-image: url('<?= CourseAvatar::getAvatar($course->getId())->getURL(Avatar::MEDIUM) ?>');"></a>
            </div>
            <div class="side">
                <a href="<?= URLHelper::getLink("dispatch.php/course/statusgroups#".$thread['metadata']['statusgruppe_id'], ['cid' => $course->getId(), 'contentbox_open' => $thread['metadata']['statusgruppe_id']]) ?>">
                    <?= htmlReady($thread->statusgruppe->name) ?>
                </a>
            </div>
        </div>

        <ul class="clean members<?= !$thread->statusgruppe->hasFolder() ? " bottomless" : "" ?>">
            <? foreach ($members as $member) : ?>
                <li>
                    <? $user = $member->user ?>
                    <? if ($user) : ?>
                        <a href="<?= URLHelper::getLink("dispatch.php/profile", ['username' => $user['username']]) ?>">
                    <? endif ?>
                    <?= Avatar::getAvatar($member['user_id'])->getImageTag(Avatar::SMALL) ?>

                    <?= $user ? htmlReady($user->getFullName()) : _("unbekannt") ?>
                    <? if ($user) : ?>
                        </a>
                    <? endif ?>
                </li>
            <? endforeach ?>
        </ul>

        <? if ($thread->statusgruppe->hasFolder()) : ?>
            <? $folder = $thread->statusgruppe->getFolder() ?>
            <div>
                <a href="<?= URLHelper::getLink("dispatch.php/course/files/index/".$folder->getId(), ['cid' => $course->getId()]) ?>">
                    <?= $folder->getIcon("clickable")->asImg(25, ['class' => "text-bottom"]) ?>
                    <?= htmlReady($folder->name) ?>
                </a>
            </div>
        <? endif ?>
    <? endif ?>

</div>
