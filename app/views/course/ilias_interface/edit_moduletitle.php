<form class="default" action="<?= $controller->url_for('course/ilias_interface/edit_moduletitle') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <label>
        <span><?= _('Seitentitel') ?></span>
        <input type="text" name="ilias_interface_moduletitle" value="<?=$ilias_interface_moduletitle?>" size="50" maxlength="50" required>
    </label>
    <footer data-dialog-button>
        <?= Studip\Button::create(_('Speichern'), 'submit') ?>
        <?= Studip\Button::createCancel(_('Schließen'), 'cancel', $dialog ? ['data-dialog' => 'close'] : []) ?>
    </footer>
</form>