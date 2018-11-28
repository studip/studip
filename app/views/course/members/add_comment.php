<? use Studip\Button, Studip\LinkButton;?>

<form class="default" action="<?=$controller->url_for(sprintf('course/members/set_comment/%s', $user->user_id))?>" method="POST">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= PageLayout::getTitle() ?></legend>

        <label>
            <?= _('Bemerkung') ?>
            <textarea name="comment" aria-label="<?=_('Bemerkung')?>" rows="8" maxlength="255" id="comment"><?=htmlReady($comment)?></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Button::createAccept(_('Speichern'), 'save'); ?>
        <?= Button::createCancel(_('Abbrechen')); ?>
    </footer>
</form>
