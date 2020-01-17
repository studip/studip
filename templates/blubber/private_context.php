<div class="blubber_private_info indented">

    <div class="icon">
        <?= Icon::create("group3", "info")->asImg(50, ['title' => _("Dies ist ein privater Blubber.")]) ?>
    </div>

    <ul class="clean members">
        <? foreach ($mentions as $mention) : ?>
        <li>
            <? if ($mention['external_contact']) : ?>

            <? else : ?>
                <? $user = User::find($mention['user_id']) ?>
                <? if ($user) : ?>
                    <? if ($user->getId() !== $GLOBALS['user']->id && count($mentions) > 2) : ?>
                        <a class="float_right" href="<?= URLHelper::getLink("dispatch.php/blubber/write_to/".$user->getId()) ?>" data-dialog title="<?= _("Anblubbern") ?>">
                            <?= Icon::create("blubber", "clickable")->asImg(20, ['class' => "text-bottom"]) ?>
                        </a>
                    <? endif ?>
                    <? if ($user->getId() === $GLOBALS['user']->id) : ?>
                        <a class="float_right"
                           href="<?= URLHelper::getLink("dispatch.php/blubber/leave_private/".$thread->getId()) ?>"
                           data-dialog="size=auto"
                           title="<?= _("Gruppe verlassen") ?>"
                           data-confirm="<?= _("Private Konversation wirklich verlassen?") ?>">
                            <?= Icon::create("door-leave", "clickable")->asImg(20, ['class' => "text-bottom"]) ?>
                        </a>
                    <? endif ?>
                    <a href="<?= URLHelper::getLink("dispatch.php/profile", ['username' => $user['username']]) ?>">
                <? endif ?>
                    <?= Avatar::getAvatar($mention['user_id'])->getImageTag(Avatar::SMALL) ?>

                    <?= $user ? htmlReady($user->getFullName()) : _("unbekannt") ?>
                <? if ($user) : ?>
                </a>
                <? endif ?>
            <? endif ?>
        </li>
        <? endforeach ?>
        <li>
            <a href="<?= URLHelper::getLink("dispatch.php/blubber/add_member_to_private/".$thread->getId()) ?>" data-dialog>
                <?= Icon::create("add", "clickable")->asImg(25, ['class' => "text-bottom"]) ?>
            </a>
        </li>
    </ul>



</div>
<? if (!$GLOBALS['perm']->have_perm("admin")) : ?>
    <div class="indented new_section">
        <a href="<?= URLHelper::getLink("dispatch.php/blubber/private_to_studygroup/".$thread->getId()) ?>" data-dialog="size=auto">
            <?= _("Aus diesem Blubber eine Studiengruppe machen.") ?>
        </a>
    </div>
<? endif ?>