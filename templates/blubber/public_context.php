<div class="blubber_public_info indented lowprio_info">
    <?= _("Dies ist ein öffentlicher Blubber und kann von allen gelesen werden.") ?>

    <? if (($thread['user_id'] === $GLOBALS['user']->id) || $GLOBALS['perm']->have_perm("root")) : ?>
        <div class="center blubber-edit-icons">
            <a href="<?= URLHelper::getLink("dispatch.php/blubber/compose/".$thread->getId()) ?>"
               data-dialog
               title="<?= _('Blubber bearbeiten') ?>">
                <?= Icon::create("edit", "clickable")->asImg(30) ?>
            </a>
            <form action="<?= URLHelper::getLink("dispatch.php/blubber/delete/".$thread->getId()) ?>"
                  method="post"
                  data-confirm="<?= _('Wirklich löschen?') ?>">
                <?= CSRFProtection::tokenTag() ?>
                <?= Icon::create("trash", "clickable")->asInput(30, ['title' => _('Diesen Blubber löschen.')]) ?>
            </form>
        </div>
    <? endif ?>
</div>
