<form class="default" action="<?= $controller->link_for('course/members/set_comment/' . $user->user_id)?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= PageLayout::getTitle() ?></legend>

        <label>
            <?= _('Bemerkung') ?>
            <textarea name="comment" aria-label="<?=_('Bemerkung')?>" rows="8" maxlength="255" id="comment"><?=htmlReady($comment)?></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for('course/members')
        ) ?>
    </footer>
</form>
